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
use App\Models\wbscte\TheorySubject;
use Illuminate\Support\Facades\Validator;
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
                    'academic_year'   => 'required',
                    // 'exam_year'    => 'required',
                    'inst_id'    => 'required',
                    'semester'       => 'required',
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
                                $inst_id = $request->inst_id;
                                $course = $request->course;
                                $semester = $request->semester;
                                $academic_session = $request->academic_year;
                                $isAdmin = $user_data->u_inst_id == $inst_id;
                                if($isAdmin){
                                    $enrollment_data = Student::where('student_inst_id', $inst_id)
                                    ->where('student_semester', $semester)
                                    ->where('student_session_yr', $academic_session)
                                    ->where('student_status_s1', 6)
                                    ->orderBy('student_reg_year', 'DESC')
                                    ->orderBy('student_reg_no', 'asc')
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
                                            'student_application_for' => (bool)optional($student->student_application_for)->is_applied,
                                            'student_exam_fees_status' => (bool)optional($student->student_exam_fees_status)->is_enrolled && (bool)optional($student->enrollment)->is_paid,
                                        ];
                                    });
                                
                                    DB::commit();
    
                                    return response()->json([
                                        'error'     =>  false,
                                        'message'   =>  'Student list found',
                                        'student_list'=> $enrollment_data,
    
                                    ]);

                                }else{
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'User is NOT an admin of this institute',
                                        
                                    ]);
                                }

                             
                            } catch (Exception $e) {


                                DB::rollback();
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'An error has occurred' //$e->getMessage()
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

                    if (in_array('enroll-submit', $url_data)) { 
                        try {
                              DB::beginTransaction();
                              $validator = Validator::make($request->all(), [
                                'academic_year'   => 'required',
                                'paying_for' => 'required',
                                'inst_id'    => 'required',
                                'semester'       => 'required',
                                'course'       => 'required',
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
                                $semester = $request->semester;
                                // $logged_in_user_id = $user_id;
                                $fees_amount = $this->calculateFees($paying_for, $semester, $course);
                              
                                if ($fees_amount > 0) {
                                    // dd("fees");
                                    // DB::beginTransaction();
                                 
                                        foreach ($request->student_info as $student) {
                                            $this->studentEnrollment($student, $inst_id, $semester,$course,$academic_year, $fees_amount, $paying_for,$user_id);
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
                        // }
                        
                 
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
    private function calculateFees($paying_for, $semester, $course)
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
            'cf_semester' => $semester,
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
            'semester' => $semester
        ])
        ->where('start_at', '<=', currentDateTime())
        ->where('end_at', '>=', currentDateTime())
        ->first();
    
        if (!$schedule) {
            // Try fallback schedule (not within start/end, just any config)
            $schedule = CnfgMarks::where([
                'config_for' => $paying_for,
                'semester' => $semester
            ])->first();
        }
    
        if ($schedule && $schedule->late_start_at && $schedule->late_start_at < currentDateTime()) {
            // Get late fees if it's past late_start_at
            $late_fees_config = ConfigFees::where([
                'cf_fees_type' => $paying_for,
                'cf_semester' => $semester,
                'cf_fees_code' => $late_fees_code
            ])->first();
    
            if ($late_fees_config) {
                $late_fees = $late_fees_config->cf_fees_amount;
            }
        }
    
        return $base_fees + $late_fees;
    }
    
    private function studentEnrollment($value, $inst_id, $semester, $course, $academic_year, $fees_amount, $paying_for,$user_id)
    {
        $reg_no = $value['reg_no'];
        $reg_year = $value['reg_year'];
      
       
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
      
        $enrolled_data = $student_data->enrollment()->create([
            'reg_no' => $reg_no,
            'reg_year' => $reg_year,
            'inst_id' => $inst_id,
            'academic_year' => $academic_year,
            'semester' => $semester,
            'course_id' => $course,
            'is_enrolled' => 1,
            'session_year' => $academic_year,
            'enrolled_at' => now(),
            'enrolled_by' => $user_id
        ]);
        // dd($enrolled_data);
        
        // dd(DB::getQueryLog());
        if($enrolled_data){
            // dd($fees_amount, $paying_for, $inst_id, $course, $academic_year, $semester);
           
            Fees::Create([
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
}
