<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Institute;
use App\Models\wbscte\CnfgMarks;
use App\Models\wbscte\ConfigFees;
use App\Models\wbscte\RegisterFees;
use App\Models\wbscte\Course;
use App\Models\wbscte\EligibilityExam;
use App\Models\wbscte\Token;
use App\Models\wbscte\User;
use App\Models\wbscte\Student;
use App\Http\Resources\wbscte\StudentAdmissionResource;
use App\Http\Resources\wbscte\StudentResource;
use App\Mail\StudentMail;
use App\Mail\StudentStatusMail;
use App\Mail\StudentApprovedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Exception;
use Validator;
use DB;


class AdmissionController extends Controller
{
    public function semOneadmissionFormSubmit(Request $request)
    {  
                    $rules = [
                        'student_inst_id' => ['required'],
                        'student_fname' => ['required'],
                        'student_lname' => ['required'],
                        'student_marital' => ['required'],
                        'student_mobile_no' => ['required', 'digits:10', 'unique:wbscte_other_diploma_student_master_tbl,student_mobile_no'],
                        'student_email' => ['required', 'email'],
                        'student_aadhar_no' => ['required', 'digits:12'],
                        'student_address' => ['required'],
                        'student_pin_code' => ['required', 'digits:6'],
                        'student_state' => ['required'],
                        'student_district' => ['required'],
                        'student_father_name' => ['required'],
                        'student_mother_name' => ['required'],
                        'student_dob' => ['required', 'date'], 
                        'student_guardian_name' => ['required'],
                        'student_gender' => ['required'],
                        'student_caste' => ['required'],
                        'student_religion' => ['required'], 
                        'student_course_id' => ['required'], 
                        'student_citizenship' => ['required'], 
                        'student_pwd' => ['required'], 
                        'student_subdivision' => ['required'], 
                        'student_highest_qualification' => ['required'], 
                        'exam_board' => ['required'],
                        'exam_pass_yr' => ['required'],
                        'exam_tot_marks' => ['required', 'numeric', 'min:1','max:999'], 
                        'obtained_marks' => ['required','numeric','min:1','max:999', 'lte:exam_tot_marks'],
                        'exam_result' => ['required'],
                        'student_image' => ['required'],
                        'student_signature' => ['required'],

                    ];
                    if (($request->student_course_id == 'ADCS' && $request->student_highest_qualification == 'GSC') ||
                        (in_array($request->student_course_id, ['ISP', 'ISF']) && $request->student_highest_qualification == 'DSC')
                    ) {
                        $rules['physc_marks'] = ['required', 'numeric','min:1','max:999'];
                        $rules['chem_marks'] = ['required', 'numeric','min:1','max:999'];
                        $rules['math_marks'] = ['required', 'numeric','min:1','max:999'];
                    }
                    
                    // **PDME Course - Requires Additional Subject Marks**
                    if ($request->student_course_id == 'PDME') {
                        $rules['physc_marks'] = ['required', 'numeric', 'min:1','max:999'];
                        $rules['chem_marks'] = ['required', 'numeric','min:1','max:999'];
                        $rules['zoology_marks'] = ['required', 'numeric','min:1','max:999'];
                        $rules['math_marks'] = ['required', 'numeric','min:1','max:999'];
                        $rules['computer_science_marks'] = ['required', 'numeric','min:1','max:999'];
                    }
                    $validator = Validator::make($request->all(), $rules, [
                        'student_mobile_no.unique' => 'Phone number already taken.',
                        'physc_marks.required' => 'Physics marks are required for this course.',
                        'chem_marks.required' => 'Chemistry marks are required for this course.',
                        'math_marks.required' => 'Mathematics marks are required for this course.',
                        'zoology_marks.required' => 'Zoology marks are required for PDME.',
                        'computer_science_marks.required' => 'Computer Science marks are required for PDME.',
                        'obtained_marks.lte' => 'Obtained marks cannot be greater than total marks.',
                        // 'exam_tot_marks.digit' => 'Total marks must be a number .',
                    ]);
        
                        if ($validator->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validator->errors()->first()
                            ]);
                        }
                        DB::beginTransaction();
                        try {
                            $course_id = $request->student_course_id;
                            // dd($course_id);
                            $student_dob = $request->student_dob;
                            $user_role_id = 4;

                            $currentYear = date('Y'); 
                            $nextYear = date('Y', strtotime('+1 year'));
                            $formattedYear = substr($nextYear, -2);
                            $session_year = $currentYear . '-' . $formattedYear;
                            $now = date("Y-m-d H.i.s");
                            $obtainedMarks = (int)$request->obtained_marks;
                            $totalMarks = $request->exam_tot_marks;
                            $percentage = ($obtainedMarks * 100) / $totalMarks;
                            $applicationNumber = generateApplicationNumber();
                            $adm_open_status = CnfgMarks::where('config_for', 'APPLICATION')
                                                        ->where('start_at', '<=', $now)  
                                                        ->where('end_at', '>=', $now)   
                                                        ->where('semester', 'Semester_I')
                                                        ->exists();  
                            //Check Late fee
                            $late_fees = CnfgMarks::where('config_for', 'APPLICATION')
                                                    ->where('late_start_at', '<=', $now) 
                                                    ->where('semester', 'Semester_I')
                                                    ->exists(); 
                            $course = Course::select('course_code')->where('course_id_pk', $course_id)->first();
                            // dd($course);
                          
                            $results = ConfigFees::select('cf_fees_code', 'cf_fees_type', 'cf_fees_amount')
                                        ->distinct()
                                        ->where('cf_semester', 'Semester_I')
                                        ->where('cf_fees_type', 'APPLICATION');
                                        if($course == 'HMCT'){
                                            if ($request->student_gender == "FEMALE") {
                                                $results = $results->where('cf_fees_code', 'APPLKAN');
                                            }
                                            else{
                                                $results = $results->where('cf_fees_code', 'APPLHMCT');
                                            }
                                        }
                                        else{
                                            if ($request->student_gender == "FEMALE") {
                                                $results = $results->where('cf_fees_code', 'APPLKAN');
                                            }
                                            else{
                                                $results = $results->where('cf_fees_code', 'APPLOTH');
                                            }
                                        }
                                       $total_fees =  $results->get();
                                      
                                if($request->student_citizenship == 'INDIAN'){
                                    if ($course['course_code'] == 'ADCS' && $request->student_highest_qualification == 'GSC') 
                                    {
                                        $marks = json_encode([
                                            'physc_marks' => $request->physc_marks,
                                            'chem_marks' => $request->chem_marks,
                                            'math_marks' => $request->math_marks
                                        ]);
                                        $sum_of_marks = ($request->physc_marks) + ($request->chem_marks) + ($request->math_marks);
                                        if($sum_of_marks > $obtainedMarks){
                                            return response()->json([
                                                'error'   => true,
                                                'message' => 'The sum of  marks must be less than Obtained Marks.',
                                            ], 400);
                                        }
                                    } elseif (($course['course_code'] == 'ISP' || $course['course_code']== 'ISF') && $request->student_highest_qualification == 'DSC') {
                                        
                                        $marks = json_encode([
                                            'physc_marks' => $request->physc_marks,
                                            'chem_marks' => $request->chem_marks,
                                            'math_marks' => $request->math_marks
                                        ]);
                                        $sum_of_marks = ($request->physc_marks) + ($request->chem_marks) + ($request->math_marks);
                                        if($sum_of_marks > $obtainedMarks){
                                            return response()->json([
                                                'error'   => true,
                                                'message' => 'The sum of  marks must be less than Obtained Marks.',
                                            ], 400);
                                        }
                                    } elseif ($course['course_code'] == 'HMCT' || $course['course_code'] == 'DVOC') {
                                        $studentDob = strtotime($request->student_dob);
                                        $cutoffYear = $currentYear - 17;
                                        $cutoffDate = strtotime("$cutoffYear-07-01");
                                        if ($studentDob > $cutoffDate) { 
                                            return response()->json([
                                                'error'   => true,
                                                'message' => "Candidate must complete 17 years on or before July 1st, $cutoffYear."
                                            ], 400);
                                        }
                                     
                                      
                                    } elseif ($course['course_code'] == 'PDME' && $request->student_highest_qualification == 'BDSC') {
                                        $marks = json_encode([
                                            'physc_marks' => $request->physc_marks,
                                            'chem_marks' => $request->chem_marks,
                                            'math_marks' => $request->math_marks,
                                            'zoology_marks' => $request->zoology_marks,
                                            'computer_science_marks' => $request->computer_science_marks,
                                            'electronics_marks' => $request->electronics_marks
                                        ]);
                                        $sum_of_marks = ($request->physc_marks) +($request->chem_marks)+($request->math_marks) + ($request->zoology_marks) +($request->computer_science_marks) +($request->electronics_marks);
                                        if($sum_of_marks > $obtainedMarks){
                                            return response()->json([
                                                'error'   => true,
                                                'message' => 'The sum of marks must be less than Obtained Marks.',
                                            ], 400);
                                        }
                                        $studentDob = strtotime($request->student_dob);
                                        $cutoffYear = $currentYear - 18;
                                        $cutoffDate = strtotime("$cutoffYear-07-01");
                                    
                                        if ($studentDob > $cutoffDate) {
                                            return response()->json([
                                                'error'   => true,
                                                'message' => "Candidate must be at least 18 years old on or before July 1st, $cutoffYear."
                                            ], 400);
                                        }
                                    
                                        if ($request->student_caste == 'SC' || $request->student_caste == 'ST') {
                                            if ($percentage < 45) {
                                                return response()->json([
                                                    'error'   => true,
                                                    'message' => "Candidate must marks at least 45% in  to be eligible."
                                                ], 400);
                                            }
                                        } elseif ($request->student_caste == 'GENERAL' || $request->student_caste == 'OBC') {
                                            if ($percentage < 50) {
                                                return response()->json([
                                                    'error'   => true,
                                                    'message' => "Candidate must marks at least 50% in in  to be eligible."
                                                ], 400);
                                            }
                                        }

                                    } else if ($course['course_code'] == 'PDPC') {
                                        $studentDob = strtotime($request->student_dob);
                                        $cutoffYear = $currentYear - 18;
                                        $cutoffDate = strtotime("$cutoffYear-07-01");
                                        if ($studentDob > $cutoffDate) {
                                            return response()->json([
                                                'error'   => true,
                                                'message' => "Candidate must be at least 18 years old on or before July 1st, $cutoffYear."
                                            ], 400);
                                        }
                                       
                                        if ($request->student_caste == 'SC' || $request->student_caste == 'ST') {
                                            if ($percentage < 45) {
                                                return response()->json([
                                                    'error'   => true,
                                                    'message' => "Candidate must marks at least 45% in $course to be eligible."
                                                ], 400);
                                            }
                                        } elseif ($request->student_caste == 'GENERAL' || $request->student_caste == 'OBC') {
                                            if ($percentage< 50) {
                                                return response()->json([
                                                    'error'   => true,
                                                    'message' => "Candidate must marks at least 50% in in $course to be eligible."
                                                ], 400);
                                            }
                                        }
                                    }
                                }
                                // data ipload -----$imageName
                                if ($request->hasFile('student_image')) {
                                    $image = $request->file('student_image');
                                    $imageName = $applicationNumber . '_image.' . $image->getClientOriginalExtension();
                                    $image->storeAs('uploads/profile_pic', $imageName, 'public');
                                    $imagePath = 'uploads/profile_pic/' . $imageName;
                                }
                        
                                if ($request->hasFile('student_signature')) {
                                    $signature = $request->file('student_signature');
                                    $signatureName = $applicationNumber . '_signature.' . $signature->getClientOriginalExtension();
                                    $signature->storeAs('uploads/signature', $signatureName, 'public');
                                    $signaturePath = 'uploads/signature/' . $signatureName;
                                 
                                }
                             
                                // dd($signatureName);
                            if($adm_open_status){
                                $new_student = Student::create([
                                    'student_session_yr' => $session_year,
                                    'student_course_id' => $course_id,
                                    'student_semester' => 'Semester_I',
                                    'student_inst_id' => $request->student_inst_id,
                                    'student_fname' => trim($request->student_fname),
                                    'student_mname' => trim($request->student_mname),
                                    'student_lname' => trim($request->student_lname),
                                    'student_fullname' => trim($request->student_fname)." ".trim($request->student_mname)." ".trim($request->student_lname),
                                    'student_dob' => $student_dob,
                                    'student_marital' => $request->student_marital,
                                    'student_father_name' => $request->student_father_name,
                                    'student_mother_name' => $request->student_mother_name,
                                    'student_guardian_name' => $request->student_guardian_name, 
                                    'student_gender' => $request->student_gender,
                                    'student_religion' => $request->student_religion,
                                    'student_citizenship' => $request->student_citizenship,
                                    'student_pwd' => $request->student_pwd,
                                    'student_caste' => $request->student_caste,
                                    'student_kanyashree_no' => isset($student_kanyashree_no) ? $student_kanyashree_no : null,
                                    'student_aadhar_no' => trim($request->student_aadhar_no),
                                    'student_mobile_no' => trim($request->student_mobile_no),  
                                    'student_email' => trim($request->student_email),
                                    'student_address' => trim($request->student_address),
                                    'student_address2' => isset($request->student_address2) ? $request->student_address2 : null,
                                    'student_state' => trim($request->student_state),
                                    'student_district' => trim($request->student_district),
                                    'student_pin_code' => trim($request->student_pin_code),
                                    'u_role_id' => '4',
                                    'student_form_num' => $applicationNumber,
                                    'student_status_s1' => '1',
                                    'student_subdivision' => $request->student_subdivision,
                                    'student_highest_qualification' => $request->student_highest_qualification,
                                    'marks' => isset($marks) ? $marks : null,
                                    'student_profile_pic' => $imagePath,
                                    'student_signature' => $signaturePath
                                ]);
                                $saved_fees = RegisterFees::create([
                                    'rf_appl_form_num'  => $applicationNumber,
                                    'rf_appl_order_no'  => $applicationNumber.""."001",
                                    'rf_appl_cors_code' => $course->course_code,
                                    'rf_semester'       => 'Semester_I',
                                    'rf_fees_type'      => $total_fees[0]->cf_fees_type,
                                    'rf_fees_code'      => $total_fees[0]->cf_fees_code,
                                    'rf_fees_amount'    => $total_fees[0]->cf_fees_amount,
                                    'rf_fees_active'    => 1,
                                ]);
                                $eligibility = EligibilityExam::create([
                                    'exam_appl_form_num' => $applicationNumber,
                                    'exam_board' => $request->exam_board,
                                    // 'exam_roll_num' => $request->exam_roll_num,
                                    'exam_pass_yr' => $request->exam_pass_yr,
                                    'exam_tot_marks'=> $request->total_marks,
                                    'exam_ob_marks'=> $request->obtained_marks,
                                    'exam_result' => $request->exam_result,
                                    'exam_per_marks' => $request->exam_per_marks,
                                    'exam_elgb_code'=>$request->student_highest_qualification,
                                ]);
                                $this->sendMail(
                                    $new_student->student_status_s1,
                                    $request->student_fname,
                                    $applicationNumber,
                                    $request->student_email
                                );
                                DB::commit();
                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  "Application Submitted successfully"
                                ], 200);
                            }
                            else{
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  "Application Time expired."
                                ], 200);
                            }
                            
                        } 
                        catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'message' => $e->getMessage()
                                )
                            );
                        }   
    }
    public function viewStudentData(Request $request , $form_num,$inst_id = null,)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('view-student', $url_data)) { 
                        if($inst_id){
                            $is_inst_admin = User::where('u_id', $user_id)->where('u_inst_id', $inst_id)->exists();
                            if($is_inst_admin){
                             $student_adm_list = Student::where([
                                'student_inst_id' => $inst_id,
                                'student_form_num'  => $form_num
                             ])
                             ->with('course','institute:inst_sl_pk,institute_name')
                             ->orderBy('student_id_pk')
                             ->first();
                          
                            }else{
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No student available'
                                );
                                return response(json_encode($reponse), 200);

                            }
                        }else{
                            $student_adm_list = Student::where('student_form_num',$form_num)
                             ->with('course','institute:inst_sl_pk,institute_name','state:state_id_pk','district:district_id_pk','subdivision:id')
                             ->orderBy('student_id_pk')
                             ->first();
                             
                        }
                        if ($student_adm_list) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student Admission list found',
                                'list'  =>  new StudentResource($student_adm_list)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No student available'

                            );
                            return response(json_encode($reponse), 200);
                        }
                       
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }

    }
    public function admissionListByInstitute(Request $request,$inst_id = null)
    { 
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('admission-list', $url_data)) { 
                        if($inst_id){
                            $is_inst_admin = User::where('u_id', $user_id)->where('u_inst_id', $inst_id)->exists();
                            
                            if($is_inst_admin){
                             $student_adm_list = Student::where([
                                'student_inst_id' => $inst_id
                             ])
                             ->with('course','institute:inst_sl_pk,institute_name')
                             ->orderBy('student_id_pk','desc')
                             ->get()
                             ->map(function($data){
                                //  dd($data);
                                return [
                                    'form_num'=> $data->student_form_num,
                                    'name'=>$data->student_fullname,
                                    'inst_name'=>optional($data->institute)->institute_name,
                                    'course_name'=>optional($data->course)->course_name,
                                    'is_applied' => $data->student_status_s1 == 1,
                                    'is_paid'=>$data->student_status_s1 == 2,
                                    'is_verified'=>$data->student_status_s1 == 3,
                                    'is_updated' => $data->student_status_s1 == 4,
                                    'is_reg_fees'=>$data->student_status_s1 == 5,
                                    'is_approved' => $data->student_status_s1 == 6,
                                    'is_rejected'=>$data->student_status_s1 == 9,
                                    'is_approved_all'=>$data->student_status_s1 == 6,
                                  
                                ];
                             });
                            //  dd($student_adm_list);
                   
                            }else{
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No student available'
                                );
                                return response(json_encode($reponse), 200);

                            }
                        }else{
                            $student_adm_list = Student::with('course','institute:inst_sl_pk,institute_name')
                             ->orderBy('student_id_pk','desc')
                             ->get()
                             ->map(function($data){
                                return [
                                    'form_num'=> $data->student_form_num,
                                    'name'=>$data->student_fullname,
                                    'inst_name'=>optional($data->institute)->institute_name,
                                    'course_name'=>optional($data->course)->course_name,
                                    'is_applied' => $data->student_status_s1 == 1,
                                    'is_paid'=>$data->student_status_s1 == 2,
                                    'is_verified'=>$data->student_status_s1 == 3,
                                    'is_updated' => $data->student_status_s1 == 4,
                                    'is_reg_fees'=>$data->student_status_s1 == 5,
                                    'is_approved' => $data->student_status_s1 == 6,
                                    'is_rejected'=>$data->student_status_s1 == 9,
                                    'is_approved_all' => $data->student_status_s1 == 6,
                                ];
                             });
                            //  dd($student_adm_list);
                        }
                        if (sizeof($student_adm_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student Admission list found',
                                'count'     =>   sizeof($student_adm_list),
                                'list'  =>   $student_adm_list
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No student available'

                            );
                            return response(json_encode($reponse), 200);
                        }
                       
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }


    }
    public function applicationVerificationInstitute(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $user_id)->first();
               
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');
              
                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('adm-verify-status', $url_data)) { 
                            $form_num = $request->form_num;
                            $remarks = $request->remarks ; 
                            $status = $request->status; 
                        if ($status == "VERIFIED") {
                            $student_data = Student::where('student_form_num', $form_num)->with([
                                'institute' => function ($query) {
                                $query->select('inst_sl_pk', 'institute_name');
                            }])->first();
                            if (!$student_data) {
                                return response()->json([
                                    'error'   => true,
                                    'message' => 'Student not found'
                                ], 404);
                            }
                            $update_data = [];
                            $message = '';
                            $total_fees = null;
                            $update_data = [
                                'student_status_s1'             => 3,
                                'student_approve_reject_status' => 'approved'
                            ];
                            $course = Course::where('course_id_pk', $student_data->student_course_id)
                            ->value('course_code'); 
                            $adm_open_status = CnfgMarks::where('config_for', 'REGISTRATION')
                                                        ->where('start_at', '<=', $now)  
                                                        ->where('end_at', '>=', $now)   
                                                        ->where('semester', 'Semester_I')
                                                        ->exists(); 
                            if($adm_open_status)
                            {
                                $results = ConfigFees::select('cf_fees_code', 'cf_fees_type', 'cf_fees_amount')
                                    ->distinct()
                                    ->where('cf_semester', 'Semester_I')
                                    ->where('cf_fees_type', 'REGISTRATION');
                                    if($course == 'HMCT'){
                                        if ($student_data->student_gender == "FEMALE") {
                                            $results = $results->where('cf_fees_code', 'REGHMCTKANS1');
                                        }
                                        else{
                                            $results = $results->where('cf_fees_code', 'REGHMCTS1');
                                        }
                                    }
                                    else{
                                        if ($student_data->student_gender == "FEMALE") {
                                            $results = $results->where('cf_fees_code', 'REGKANS1');
                                        }
                                        else{
                                            $results = $results->where('cf_fees_code', 'REGOTHS1');
                                        }
                                    }
                                

                            }else{
                                $results = ConfigFees::select('cf_fees_code', 'cf_fees_type', 'cf_fees_amount')
                                    ->distinct()
                                    ->where('cf_semester', 'Semester_I')
                                    ->where('cf_fees_type', 'REGISTRATION');
                                    if($course == 'HMCT'){
                                        $results = $results->where('cf_fees_code', 'REGLTFHMCTS1');
                                       
                                    }
                                    else{   
                                        $results = $results->where('cf_fees_code', 'REGLTFOTHS1');
                                        
                                    }
                                
                            }
                            
                            $total_fees =  $results->get();
                            if($total_fees){
                                $saved_fees = RegisterFees::create([
                                    'rf_appl_form_num'  => $student_data->student_form_num,
                                    'rf_appl_order_no'  => $student_data->student_form_num.""."002",
                                    'rf_appl_cors_code' => $course,
                                    'rf_semester'       => 'Semester_I',
                                    'rf_fees_type'      => $total_fees[0]->cf_fees_type,
                                    'rf_fees_code'      => $total_fees[0]->cf_fees_code,
                                    'rf_fees_amount'    => $total_fees[0]->cf_fees_amount,
                                    'rf_fees_active'    => 1,
                                ]);
                            }
                            $message = 'Student admission approved successfully';

                        } elseif ($status == "REJECT") {
                       
                            $update_data = [
                                'student_status_s1'             => 9,
                                'student_approve_reject_status' => $remarks,
                            ];
                            $message = 'Student admission rejected with remarks';
                        } else {
                            return response()->json([
                                'error'   => true,
                                'message' => 'Invalid status provided'
                            ], 400);
                        }
                        Student::where('student_form_num', $form_num)->update($update_data);
                        $this->sendMail(
                            $update_data['student_status_s1'],
                            $form_num,
                            $student_data->institute->institute_name,
                            $student_data->student_email
                        );
                        auditTrail($user_id, "$form_num, $status - Added successfully");

                    
                        return response()->json([
                            'error'   => false,
                            'message' => $message,
                            // 'data' =>  $updated_student
                        ], 200);
                       
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }
        
    }
    // public function uploadImageSign(Request $request)
    // {
       
    //     $validator = Validator::make($request->all(), [
    //         'student_image' => 'required',
    //         'student_signature' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => $validator->errors()], 422);
    //     }
    //     $form_num = $request->form_num;
    //     $student = Student::where('student_form_num',$form_num)->first();
    //     $uploadedFiles = [];
    //     $updated = false; // Track if any updates are made

    //     if ($request->hasFile('student_image')) {
    //         $image = $request->file('student_image');
    //         $imageName = $form_num . '_image.' . $image->getClientOriginalExtension();
    //         $imagePath = 'uploads/profile_pic/' . $imageName;
            
    //         $image->storeAs('uploads/profile_pic', $imageName, 'public');
    //         $student->student_profile_pic = $imagePath;
    //         $updated = true;
    //     }

    //     if ($request->hasFile('student_signature')) {
    //         $signature = $request->file('student_signature');
    //         $signatureName = $form_num . '_signature.' . $signature->getClientOriginalExtension();
    //         $signaturePath = 'uploads/signature/' . $signatureName;
            
    //         $signature->storeAs('uploads/signature', $signatureName, 'public');
    //         $student->student_signature = $signaturePath;
    //         $updated = true;
    //     }

    //     if ($updated) {
    //         $student->student_status_s1 = 4;
    //         $student->save();
    //         auditTrail($form_num, "{$form_num} - Profile pic and signature updated successfully");
            
    //         return response()->json([
    //             'error' => false,
    //             'message' => 'Profile photo and signature uploaded successfully!',
    //         ], 200);
    //     }

    //     return response()->json([
    //         'error' => true,
    //         'message' => 'No file uploaded.',
    //     ], 400);
                
        
        
    // }
    public function approvedCouncil(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('approve-council', $url_data)) { 
                        $form_num = $request->form_num;
                        $status = $request->status; 
                    
                        if ($status == "APPROVED") {
                            $student_data = Student::where('student_form_num', $form_num)->with([
                                'institute' => function ($query) {
                                $query->select('inst_sl_pk', 'institute_name');
                            }])->first();
                            // dd($student_data);
                                if (!$student_data) {
                                    return response()->json([
                                        'error'   => true,
                                        'message' => 'Student not found'
                                    ], 404);
                                }
                    
                            try {
                                DB::beginTransaction(); 
                                $newRegNo = generateRegistrationNo($form_num);
                                $year = date('Y'); // Example: 2025
                                $nextYearLastTwo = substr((string)($year + 1), -2); 
                                $reg_yr = $year . '-' . $nextYearLastTwo; 
                                if($student_data->student_status_s1 == 5){
                                    $student_data->update([
                                        'student_status_s1' => 6,
                                        'student_reg_no' => $newRegNo,
                                        'student_reg_year'=>  $reg_yr 
                                    ]);
                                    // dd($student_data->institute->institute_name);
                                    $this->sendMail(
                                        $student_data->student_status_s1,  
                                        $student_data->student_name,       
                                        $form_num,                        
                                        $student_data->student_email,     
                                        $student_data->institute->institute_name,  
                                        $newRegNo      
                                    );
                                    auditTrail($user_id, "$form_num - Registration number successfully generated: $newRegNo");

                                    DB::commit();
                                    return response()->json([
                                        'error'   => false,
                                        'message' => 'Student admission approved successfully'
                                    ], 200);
                                }else{
                                    return response()->json([
                                        'error'   => true,
                                        'message' => 'Student fees not paid'
                                    ], 200);

                                }
                    
                            } catch (\Exception $e) {
                                DB::rollback(); // Rollback if something fails
                                return response()->json([
                                    'error'   => true,
                                    'message' => 'An error occurred while updating the student status',
                                    'details' => $e->getMessage()
                                ], 500);
                            }
                        }
                    
                    
                       
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }

    }
    public function approvedAll(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('approve-all', $url_data)) { 
                        $form_nums = $request->form_nums;
                        $status = $request->status; 
                        
                        if ($status == "APPROVED" && is_array($form_nums) && count($form_nums) > 0) {
                                    
                                $students = Student::whereIn('student_form_num', $form_nums)
                                        ->with(['institute' => function ($query) {
                                            $query->select('inst_sl_pk', 'institute_name');
                                        }])->get();

                                    if ($students->isEmpty()) {
                                        return response()->json([
                                            'error' => true,
                                            'message' => 'No students found'
                                        ], 404);
                                    }

                                    try {
                                        DB::beginTransaction();
                                        $approvedStudents = [];

                                        foreach ($students as $student) {
                                            if ($student->student_status_s1 == 5) {
                                                $newRegNo = generateRegistrationNo($student->student_form_num);
                                                $year = date('Y'); 
                                                $nextYearLastTwo = substr((string)($year + 1), -2);
                                                $reg_yr = $year . '-' . $nextYearLastTwo;

                                                $student->update([
                                                    'student_status_s1' => 6,
                                                    'student_reg_no' => $newRegNo,
                                                    'student_reg_year' => $reg_yr
                                                ]);

                                                // Send email
                                                $this->sendMail(
                                                    $student->student_status_s1,  
                                                    $student->student_name,       
                                                    $student->student_form_num,  
                                                    $student->student_email,     
                                                    $student->institute->institute_name,  
                                                    $newRegNo      
                                                );

                                                // Log audit trail
                                                auditTrail($request->user_id, "{$student->student_form_num} - Registration number successfully generated: $newRegNo");

                                                $approvedStudents[] = $student->student_form_num;
                                          
                                           

                                                DB::commit();

                                                return response()->json([
                                                    'error' => false,
                                                    'message' => 'Selected students approved successfully',
                                                    'approved_students' => $approvedStudents
                                                ], 200);
                                              }else{
                                                return response()->json([
                                                    'error'   => true,
                                                    'message' => 'Student fees not paid'
                                                ], 200);
            
                                            }
                                        }
                                        
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        return response()->json([
                                            'error' => true,
                                            'message' => 'Approval failed. Please try again.',
                                            'details' => $e->getMessage()
                                        ], 500);
                                    }
                        }
                        
                 
                    } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                    }
                } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to non availability of token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }

    }
    public function updateStudent(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))
                ->where('t_expired_on', '>=', $now)
                ->first();
    
            if ($token_check) {  // Check if the token is expired or not
                $validator = Validator::make($request->all(), [
                    'student_inst_id' => ['required'],
                    'student_fname' => ['required'],
                    'student_lname' => ['required'],
                     'student_mobile_no' => ['required', 'digits:10', 'unique:wbscte_other_diploma_student_master_tbl,student_mobile_no,' . $request->student_form_num . ',student_form_num'],
                    'student_email' => ['required', 'email'],
                    'student_aadhar_no' => ['required', 'digits:12'],
                    'student_address' => ['required'],
                    'student_pin_code' => ['required', 'digits:6'],
                    'student_state' => ['required'],
                    'student_district' => ['required'],
                    'student_father_name' => ['required'],
                    'student_mother_name' => ['required'],
                    'student_guardian_name' => ['required'],
                    'student_course_id' => ['required'],
                    'student_subdivision' => ['required'],
                    'student_form_num' => ['required'], 
                    
                ]);
    
                if ($validator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validator->messages()
                    ], 400);
                } else {
                    $token_user_id = $token_check->t_user_id;
                    $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
    
                    $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')
                        ->where('rp_role_id', $user_data->u_role_id)
                        ->pluck('rp_url_id');
    
                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('wbscte_other_diploma_auth_urls')
                            ->where('url_visible', 1)
                            ->whereIn('url_id', $role_url_access_id)
                            ->get()
                            ->toArray();
                        $url_data = array_column($urls, 'url_name');
    
                        if (in_array('update-student', $url_data)) { // Check URL permission
                            DB::beginTransaction();
                            try {
                                $student = Student::where('student_form_num', $request->student_form_num)->first();

                                if ($student && $student->student_status > 6) {
                                    $updatedRows = Student::where('student_form_num', $request->student_form_num)
                                        ->update([
                                            'student_fname' => $request->student_fname,
                                            'student_mname' => $request->student_mname,
                                            'student_lname' => $request->student_lname,
                                            'student_inst_id' => $request->student_inst_id,
                                            'student_mobile_no' => $request->student_mobile_no,
                                            'student_email' => $request->student_email,
                                            'student_aadhar_no' => $request->student_aadhar_no,
                                            'student_address' => $request->student_address,
                                            'student_pin_code' => $request->student_pin_code,
                                            'student_state' => $request->student_state,
                                            'student_district' => $request->student_district,
                                            'student_father_name' => $request->student_father_name,
                                            'student_mother_name' => $request->student_mother_name,
                                            'student_guardian_name' => $request->student_guardian_name,
                                            'student_course_id' => $request->student_course_id,
                                            'student_subdivision' => $request->student_subdivision,
                                        ]);

                                    if ($updatedRows > 0) {
                                        auditTrail($token_check->t_user_id, "Student with Form Number {$request->student_form_num} updated.");
                                        DB::commit();
                                        return response()->json([
                                            'error' => false,
                                            'message' => 'Student data updated successfully'
                                        ], 200);
                                    } else {
                                        return response()->json([
                                            'error' => true,
                                            'message' => 'No changes were made'
                                        ], 400);
                                    }
                                } else {
                                    return response()->json([
                                        'error' => true,
                                        'message' => 'Update not allowed. council admin approved the application'
                                    ], 403);
                                }

    
                            } catch (Exception $e) {
                                DB::rollback();
                                return response()->json([
                                    'error' => true,
                                    'message' => 'An error has occurred' //$e->getMessage()
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'error' => true,
                                'message' => "Oops! You don't have sufficient permission"
                            ], 403);
                        }
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => "Oops! You don't have sufficient permission"
                        ], 403);
                    }
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Unable to process your request due to non-availability of token'
            ], 401);
        }
    }
    private function sendMail($status, $name, $form_num, $email, $inst_name = null, $reg_no = null)
    {  try {
                switch ($status) {
                    case 1:
                        Mail::to($email)->send(new StudentMail($name, $form_num, $email));
                        auditTrail($form_num, $form_num . ' - Application approved and confirmation email sent');
                        break;

                    case 3:
                    case 9:
                        Mail::to($email)->send(new StudentStatusMail($form_num, $email, $inst_name,$name));
                        auditTrail($form_num, $form_num . ' - Institute approved and confirmation email sent');
                        break;

                    case 6:
                        
                        Mail::to($email)->send(new StudentApprovedMail($name,$form_num,$email,$inst_name,$reg_no));
                        auditTrail($form_num, $reg_no . ' - Council admin approved and confirmation email sent');
                        break;

                    default:
                        break;
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }

    
    
   

    

    
}
