<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Student;
use App\Models\wbscte\Token;
use App\Models\wbscte\ConfigFees;
use App\Models\wbscte\Course;
use App\Models\wbscte\District;
use App\Models\wbscte\Paper;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Enrollment;
use App\Models\wbscte\Fees;
use App\Models\wbscte\CnfgMarks;
use Illuminate\Support\Facades\DB;
use App\Models\wbscte\User;
use Illuminate\Support\Str;
use App\Models\wbscte\TheorySubject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
class EnrollmentController extends Controller
{

    public function list(Request $request){
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    //'access_urls' => 'required|array',
                    'season_year'   => 'required',
                    'exam_year'    => 'required',
                    'inst_id'    => 'required',
                    'course'       => 'required',
                   
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages()
                    ], 400);
                } else {
                    $token_user_id = $token_check->t_user_id;
                    $user_data = User::select('u_id', 'u_ref', 'u_role_id','u_inst_id')->where('u_id', $token_user_id)->first();
                    $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');

                        if (in_array('list', $url_data)) { //check url has permission or not
                            DB::beginTransaction();
                            try {                  
                                // if('SEMESTER_I'){
                                    $inst_id = $request->inst_id;
                                    $course = $request->course;
                                    $season_year = $request->season_year;
                                    $isAdmin = $user_data->u_inst_id == $inst_id;
                                    if($isAdmin){
                                        $enrollment_count = Enrollment::where('inst_id', $inst_id)
                                        ->where('course_id', $course)
                                        ->where('academic_year', $season_year)
                                        ->where('is_enrolled', 1)
                                        ->count();
                                        
                                        $student_list = Student::where('student_inst_id', $inst_id)
                                                    ->where('student_course_id', $course)
                                                    ->where('student_session_yr', $season_year)
                                                    ->where('student_status_s1', 6)
                                                    ->orderBy('student_reg_year', 'DESC')
                                                    ->orderBy('student_reg_no', 'asc')
                                                    ->with('enrollment') 
                                                    ->get()
                                                    ->map(function ($student) {
                                                        return [
                                                            'father_name' => $student->student_father_name,
                                                            'reg_no' => $student->student_reg_no,
                                                            'fullname' => $student->student_fullname,
                                                            'mobile_no' => $student->student_mobile_no,
                                                            'mother_name' => $student->student_mother_name,
                                                            'reg_year' => $student->student_reg_year,
                                                            'student_dob' => $student->student_dob,
                                                            'is_applied' => (bool)optional($student->enrollment)->is_enrolled,
                                                            'is_enrollment' => (bool)optional($student->enrollment)->is_enrolled && (bool)optional($student->enrollment)->is_paid,
                                                        ];
                                                    });

                                        if ($student_list->isEmpty()) {
                                            DB::rollBack();
                                            return response()->json([
                                                'error' => true,
                                                'message' => 'No data found',
                                            ], 404);
                                        }else{
                                            if(count($student_list)){
                                                DB::commit();
                                                return response()->json([
                                                    'error' => false,
                                                    'message' => 'Data found',
                                                    'count' => $student_list->count(),
                                                    'is_enrolled' => (bool)$enrollment_count,
                                                    'can_be_unlocked' => $student_list->count() > $enrollment_count,
                                                    'list' => $student_list,
                                                ]);
                                            }

                                        }
    
                                    }else if($user_data->u_role_id ='1'){
                                        $enrollment_count = Enrollment::where('inst_id', $inst_id)
                                        ->where('course_id', $course)
                                        ->where('academic_year', $season_year)
                                        ->where('is_enrolled', 1)
                                        ->count();
                                        
                                        $student_list = Student::where('student_inst_id', $inst_id)
                                                    ->where('student_course_id', $course)
                                                    ->where('student_session_yr', $season_year)
                                                    ->where('student_status_s1', 6)
                                                    ->orderBy('student_reg_year', 'DESC')
                                                    ->orderBy('student_reg_no', 'asc')
                                                    ->with('enrollment') // eager loading the defined relationship
                                                    ->get()
                                                    ->map(function ($student) {
                                                        return [
                                                            'father_name' => $student->student_father_name,
                                                            'reg_no' => $student->student_reg_no,
                                                            'fullname' => $student->student_fullname,
                                                            'mobile_no' => $student->student_mobile_no,
                                                            'mother_name' => $student->student_mother_name,
                                                            'reg_year' => $student->student_reg_year,
                                                            'student_dob' => $student->student_dob,
                                                            'is_applied' => (bool)optional($student->enrollment)->is_enrolled,
                                                            'is_enrollment' => (bool)optional($student->enrollment)->is_enrolled && (bool)optional($student->enrollment)->is_paid,
                                                        ];
                                                    });

                                        if ($student_list->isEmpty()) {
                                            DB::rollBack();
                                            return response()->json([
                                                'error' => true,
                                                'message' => 'No data found',
                                            ], 404);
                                        }else{
                                            if(count($student_list)){
                                                DB::commit();
                                                return response()->json([
                                                    'error' => false,
                                                    'message' => 'Data found',
                                                    'count' => $student_list->count(),
                                                    'is_enrolled' => (bool)$enrollment_count,
                                                    'can_be_unlocked' => $student_list->count() > $enrollment_count,
                                                    'list' => $student_list,
                                                ]);
                                            }

                                        }
                                    }else{
                                        return response()->json([
                                            'error'     =>  true,
                                            'message'   =>  'User is NOT authorized to access this data',
                                            
                                        ]);
                                    }

                                // }

                             
                            } catch (Exception $e) {


                                DB::rollback();
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   => $e->getMessage()
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  "Oops! you don't have sufficient permission"
                            ], 401);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>  "Oops! you don't have sufficient permission"
                        ], 401);
                    }
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
    public function submit(Request $request)
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

                    if (in_array('submit', $url_data)) { 
                        try {
                              DB::beginTransaction();
                              $validator = Validator::make($request->all(), [
                                'academic_year'   => 'required',
                                'paying_for' => 'required',
                                'inst_id'    => 'required',
                                'course'       => 'required',
                                'exam_year' => 'required',
                            ]);
                            if ($validator->fails()) {
                                return response()->json([
                                    'error' => true,
                                    'message' => $validator->errors()->first()
                                ]);
                            }else{
                                $exam_year = $request->exam_year;
                                $academic_year = $request->academic_year;
                                $paying_for = $request->paying_for;
                                $inst_id = $request->inst_id;
                                $course = $request->course;
                                $fees_amount = $this->calculateFees($paying_for, $course);
                                    // dd($fees_amount);
                                
                                    if ($fees_amount > 0) {
                                  
                                            foreach ($request->student_info as $student) {
                                                $this->studentEnrollment($student, $inst_id,$course,$academic_year, $fees_amount, $paying_for,$user_id,$exam_year);
                                            }
                                        
                                        DB::commit();
                    
                                        return response()->json([
                                            'error' => false,
                                            'message' => "Enrolled Successfully"
                                        ], 200);
                                    } else {
                                     
                                        return response()->json([
                                            'error' => true,
                                            'message' => 'Enrollment Fees Not Configured in the system'
                                        ]);
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
    private function calculateFees($paying_for, $course)
    {
        $course_row = Course::select('course_code')->where('course_id_pk', $course)->first();
    
        if (!$course_row) {
            throw new \Exception('Course not found');
        }
    
        $course_code = $course_row->course_code;
    
        if ($course_code === 'HMCT') {
            $fees_code = 'EXMHMCTS1';
            $late_fees_code = 'EXAMLHMCT';
        } else {
            $fees_code = 'EXMOTHS1';
            $late_fees_code = 'EXAMLOTH';
        }
    
        // Get regular fees
        $fees_cnfg = ConfigFees::where([
            'cf_fees_type' => $paying_for,
            'cf_semester' => 'SEMESTER_I',
            'cf_fees_code' => $fees_code
        ])->first();
    
        if (!$fees_cnfg) {
            return 0; // Regular fees not configured
        }
    
        $base_fees = $fees_cnfg->cf_fees_amount;
        $late_fees = 0;
    
        // Try to get schedule
        $schedule = CnfgMarks::where([
            'config_for' => $paying_for,
            'semester' => 'SEMESTER_I',
        ])
        ->where('start_at', '<=', currentDateTime())
        ->where('end_at', '>=', currentDateTime())
        ->first();
    
        if (!$schedule) {
            // Try fallback schedule (not within start/end, just any config)
            $schedule = CnfgMarks::where([
                'config_for' => $paying_for,
                'semester' => 'SEMESTER_I',
            ])->first();
        }
    
        if ($schedule && $schedule->late_start_at && $schedule->late_start_at < currentDateTime()) {
            // Get late fees if it's past late_start_at
            $late_fees_config = ConfigFees::where([
                'cf_fees_type' => $paying_for,
                'cf_semester' => 'SEMESTER_I',
                'cf_fees_code' => $late_fees_code
            ])->first();
    
            if ($late_fees_config) {
                $late_fees = $late_fees_config->cf_fees_amount;
            }
        }
    
        return $base_fees + $late_fees;
    }
    
    private function studentEnrollment($value, $inst_id, $course, $academic_year, $fees_amount, $paying_for,$user_id,$exam_year)
    
    {
        $reg_no = $value['reg_no'];
        $reg_year = $value['reg_year'];
        $semester = 'SEMESTER_I';
      
       
        $student_data = Student::where([
            'student_reg_no' => $reg_no,
            'student_reg_year' => $reg_year,
            'student_session_yr' => $academic_year,
        ])->with([
            'enrollment' => function ($query) use ($semester,$academic_year,$reg_year) {
                $query->where([
                    'semester' => $semester,
                    'reg_year' => $reg_year,
                    'academic_year' => $academic_year
                ]);
            }
        ])->first();
        $enrolled_data = $student_data->enrollment()->updateOrCreate([
            'reg_no' => $reg_no,],[
            'reg_year' => $reg_year,
            'inst_id' => $inst_id,
            'academic_year' => $academic_year,
            'semester' => $semester,
            'course_id' => $course,
            'is_enrolled' => 1,
            'session_year' => $academic_year,
            'enrolled_at' => now(),
            'enrolled_by' => $user_id,
            'exam_year' => $exam_year
        ]);
        
        if($enrolled_data){
            Fees::updateOrCreate([
                'reg_no' => $reg_no,
                'inst_id' => $inst_id,
                'course_id' => $course,
                'type' => $paying_for,
                'amount' => $fees_amount,
                'academic_session' => $academic_year,
                'semester' => $semester,
                'created_at' => now(),
            ]);
           
            auditTrail($user_id, "Student of Reg.No {$reg_no} Promoted to {$course} whose institute: {$inst_id}");
        }
        

    }
    public function downloadPdf(Request $request,)
    {
        $inst_id = $request->inst_id;
        $inst_name = $request->inst_name;
        $course_id = $request->course;
        $academic_year = $request->academic_year;
        $file_type = $request->file_type; 
        $course = Course::select('course_code','course_name')->where('course_id_pk', $course_id)->first();
       $course_code = $course->course_code;
       $course_name = $course->course_name;
        $semester = 'SEMESTER_I';
        if($course_code == 'HMCT'){
            $fees_cnfg = ConfigFees::where([
                'cf_fees_type' => 'EXAMINATION',
                'cf_fees_code' => 'EXMHMCTS1',
                'cf_semester' => $semester,
            ])->first();
    
           }else{
            $fees_cnfg = ConfigFees::where([
                'cf_fees_type' => 'EXAMINATION',
                'cf_fees_code' => 'EXMOTHS1',
                'cf_semester' => $semester,
            ])->first();
           }
            if (!$fees_cnfg) {
                return response()->json([
                    'error' => true,
                    'message' => 'Enrollment Fees Not Configured in the system'
                ]);
           }
              
    
            $enrollment_fees = $fees_cnfg->cf_fees_amount;
            // $late_fees = $fees_cnfg->late_fees;
    
            $fees_reg_nos = Fees::where([
                'inst_id' => $inst_id,
                'course_id' => $course_id,
                'type' => 'EXAMINATION',
                'academic_session' => $academic_year,
                'semester' => $semester
            ])->pluck('reg_no');
    
            $enrollments = Enrollment::where([
                'academic_year' => $academic_year,
                'inst_id' => $inst_id,
                'course_id' => $course_id,
                'semester' =>  $semester,
            ])
            ->whereIn('reg_no', $fees_reg_nos)
            ->with('student') 
            ->orderBy('reg_no', 'asc')
            ->get();
    
            if ($enrollments->count()) {
                
                if ($file_type == 'ENROLLMENT_SHEET') {
                    $enroll_list = $enrollments
                        ->map(function ($value, $key) {
                            return [
                                'sl_no' => $key + 1,
                                'stu_name' => ($value->student)->student_fullname,
                                'stu_reg_no' => ($value->student)->student_reg_no,
                                'session_year' => ($value->student)->student_session_yr,
                                'stu_reg_year' => optional($value->student)->student_reg_year,
                                'guardian' => optional($value->student)->student_guardian_name,
                                'signature' => $this->studentImage($value->student)
                            ];
                        });
    
                        $pdf = Pdf::loadView('exports.enrollment-sheet', [
                            'students' => $enroll_list,
                            'inst_id' => $inst_id,
                            'course_id' => $course_id,
                            'course_name' => $course_name,
                            'course_code' => $course_code,
                            'inst_name' => $inst_name,
                            'academic_year' => $academic_year,
                            'exam_fees' => $enrollment_fees,
                            // 'late_fine' => $late_fees,
                            'semester' => $semester,
                            
                        ]);
    
                        $pdf->setPaper('A4', 'portrait');
                        $pdf->output();
                        $domPdf = $pdf->getDomPDF();
                        $canvas = $domPdf->get_canvas();
                        $canvas->page_text(10, 5, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);
    
                        return $pdf->setPaper('a4', 'portrait')
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->stream("Enrollment-Sheet.pdf");
               
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'No Data Found'
                ]);
            }

    //    }

       
       
    }
    private function studentImage($student)
    {
        if ($student->student_signature) {
            $image_path = public_path("storage/{$student->student_signature}");
        
            if (file_exists($image_path)) {
                return $image_path;
            }
        }
        
    }
    public function unlock(Request $request,)
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

                    if (in_array('unlock', $url_data)) { 
                        try {
                            DB::beginTransaction();
                            $validator = Validator::make($request->all(), [
                                'academic_year'   => 'required',
                                'inst_id'    => 'required',
                                'course'       => 'required',
                                'exam_year' => 'required',
                            ]);
                            if ($validator->fails()) {
                                return response()->json([
                                    'error' => true,
                                    'message' => $validator->errors()->first()
                                ]);
                            }else{
                                $exam_year = $request->exam_year;
                                $academic_year = $request->academic_year;
                                $inst_id = $request->inst_id;
                                $course = $request->course;
                                
                                $semester ='SEMESTER_I';
                                // {
                                    Enrollment::where([
                                        'inst_id' => $inst_id,
                                        'course_id' => $course,
                                        'semester' =>  $semester,
                                        'academic_year' =>  $academic_year,
                                        'exam_year'=>  $exam_year,
                                    ])->update(['is_locked_sem_1' => 1]);
                                    
                                // }
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'error' => true,
                                'message' => 'Unlock failed. Please try again.',
                                'details' => $e->getMessage()
                            ], 500);
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
            else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to non availability of token'
                ], 401);
        }
    }

}
