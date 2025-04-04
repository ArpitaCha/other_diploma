<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Student;
use App\Models\wbscte\Token;

use App\Models\wbscte\Course;
use App\Models\wbscte\District;
use App\Models\wbscte\Paper;
use App\Models\wbscte\Institute;
use App\Models\wbscte\CnfgMarks;
use Illuminate\Support\Facades\DB;
use App\Models\wbscte\User;
use App\Models\wbscte\TheorySubject;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\ExternelExaminerMap;
use App\Http\Resources\wbscte\UserResource;
use App\Http\Resources\wbscte\ExaminerInternalResource;
use App\Http\Resources\wbscte\StateResource;
use App\Http\Resources\wbscte\CourseResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\wbscte\DistrictResource;
use App\Http\Resources\wbscte\InstituteResource;
use App\Http\Resources\wbscte\TheoryPaperResource;
use App\Http\Resources\wbscte\ScheduleResource;
use App\Http\Resources\wbscte\MarksScheduleResource;
use App\Models\wbscte\OtherDiplomaExaminnerInstitute;

class EnrollmentController extends Controller
{

    public function list(Request $request){
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    //'access_urls' => 'required|array',
                    'academic_session'   => 'required',
                    'exam_year'    => 'required',
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
                    $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
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
                                $academic_session = $request->academic_session;
                            

                                $enrollment_data = Student::where('student_inst_id', $inst_id)
                                ->where('student_course_id', $course)
                                ->where('student_semester', $semester)
                                ->where('student_session_yr', $academic_session)
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

                                ])
                                    ;
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
    
}
