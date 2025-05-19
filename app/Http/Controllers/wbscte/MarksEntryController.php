<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use App\Models\wbscte\Attendance;
use App\Models\wbscte\MarksEntry;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\wbscte\MarksListResource;
use App\Models\wbscte\Paper;
use Barryvdh\DomPDF\Facade\Pdf;

class MarksEntryController extends Controller
{
    public function marksentrylist(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('marks-entry-list', $url_data)) {
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'course_id' => ['required'],
                            'paper_type' => ['required'],
                            'session_yr' => ['required'],
                            'paper_id' => ['required'],
                            'entry_type' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            $session_yr = $request->session_yr;
                            $semester = 'SEMESTER_I';
                            $inst_id = $request->inst_id;
                            $course_id = $request->course_id;
                            $paper_id = $request->paper_id;
                            $paper_type = $request->paper_type;
                            $subject_entry_type = $request->entry_type;

                            $marks_list = Attendance::where(['attr_sessional_yr' => $session_yr, 'att_sem' => $semester, 'att_inst_id' => $inst_id, 'att_course_id' => $course_id, 'att_paper_id' => $paper_id, 'att_paper_type' => $paper_type, 'att_paper_entry_type' => $subject_entry_type])
                                ->with('marks:id,att_id,marks,is_final_submit,internal_attendance_marks,internal_viva_marks,internal_class_test_marks', 'paperMarks:paper_id_pk,paper_full_marks,paper_internal_marks,paper_external_marks,paper_sessional_internal_viva_marks,paper_sessional_internal_class_test_marks,paper_sessional_internal_attendance_marks,paper_internal_attendance_marks,paper_internal_theory_class_test_marks,paper_sess_assign_viva_marks,paper_internal_theory_viva_marks,paper_sess_before_viva_marks', 'student:student_reg_no,student_fullname')->orderBy('att_reg_no', 'asc')
                                ->get();
                            $final_submit_count = $marks_list->where('is_final_submit', 1)->count();
                            $total_student = Attendance::where('att_inst_id',$inst_id)->where('att_course_id', $course_id)  
                            ->where('att_sem', $semester)->where('att_paper_id',$paper_id)->where('att_paper_type', $paper_type)  
                            ->where('att_paper_entry_type', $subject_entry_type)->where('attr_sessional_yr', $session_yr)->count();
                  
                                if ($total_student != $final_submit_count) {
                                        $response = [
                                            'error' => true,
                                            'message' => 'Attendance not submitted finally'
                                        ];
                                        return response()->json($response, 200);
                                    }else{
                                        $paper_marks = Paper::where('paper_id_pk', $paper_id)
                                        ->where('paper_semester', $semester)
                                        ->where('paper_category', $paper_type)
                                        ->where('inst_id', $inst_id)
                                        ->where('course_id', $course_id)
                                        ->where('is_active', 1)
                                        ->first();
                                        // return $paper_marks;

                                        if ($paper_marks) {
                                            if ($paper_type == 1  && $subject_entry_type == 1) {
                                                $max_marks = [
                                                    'internal_marks' => $paper_marks->paper_internal_marks,
                                                    'internal_attendance_marks' => $paper_marks->paper_internal_attendance_marks,
                                                    'viva_marks' =>$paper_marks->paper_internal_theory_viva_marks ,
                                                    'class_test_marks'=>$paper_marks->paper_internal_theory_class_test_marks ,
                                                    'external_marks' => $paper_marks->paper_external_marks
                                                ];
                                            } elseif ($paper_type == 2 && $subject_entry_type == 1) {
                                                $max_marks = [
                                                    'internal_marks' => $paper_marks->paper_internal_marks,
                                                    'internal_attendance_marks' => $paper_marks->paper_sessional_internal_attendance_marks,
                                                     'viva_marks' =>$paper_marks->paper_sessional_internal_viva_marks ,
                                                    'class_test_marks'=>$paper_marks->paper_sessional_internal_class_test_marks,
                                                    'external_marks' => $paper_marks->paper_external_marks
                                                ];
                                            }elseif($paper_type == 2 && $subject_entry_type == 2){
                                                $max_marks = [
                                                    'viva_marks' =>$paper_marks->paper_sess_assign_viva_marks,//before viva marks
                                                    'class_test_marks'=>$paper_marks->paper_sess_before_viva_marks,
                                                    'external_marks' => $paper_marks->paper_external_marks//assign marks
                                                 
                                                ];

                                            }elseif($paper_type == 1 && $subject_entry_type == 2){
                                                $max_marks = [
                                                    'external_marks' => $paper_marks->paper_external_marks
                                                 
                                                ];
                                            

                                            }
                                        }
                                        //  return $max_marks;
                                        // if($subject_entry_type == 2 && $paper_type == 1){
                                        //     $response = [
                                        //         'error' => false,
                                        //         'message' => 'You are Not available',
                                        //     ];
                                        //     return response()->json($response, 200);

                                        // }

                                    if (sizeof($marks_list) > 0) {
                                        $response = [
                                            'error' => false,
                                            'message' => 'Data found',
                                            'marks' => MarksListResource::collection($marks_list),
                                            'max_marks' => $max_marks
                                        ];
                                        return response()->json($response, 200);
                                    } else {
                                        $response = [
                                            'error' => true,
                                            'message' => 'No Data available'
                                        ];
                                        return response()->json($response, 200);
                                    }
                                } 

                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
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

    public function marksUpdate(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('marks-update', $url_data)) {
                        $marks_list = $request->all();
                       
                        try {
                            DB::beginTransaction();
                            foreach ($marks_list as $marks) {
                                $validated = Validator::make($marks, [
                                    'attendence_id' => ['required'],
                                    'marks_id' => ['nullable'],
                                    'reg_no' => ['required'],
                                    'inst_id' => ['required'],
                                    'course_id' => ['required'],
                                    'semester' => ['required'],
                                    'session_yr' => ['required'],
                                    'paper_id' => ['required'],
                                    'paper_type' => ['required'],
                                    'entry_type' => ['required'],
                                    'internal_marks' => ['nullable'],
                                    'attendance_marks' => ['nullable'],
                                    'theory_viva_marks' => ['nullable'],
                                    'theory_test_marks' => ['nullable'],
                                ]);
                                if ($validated->fails()) {
                                    return response()->json([
                                        'error' => true,
                                        'message' => $validated->errors()
                                    ]);
                                } else {
                                    $attendance_marks = $marks['attendance_marks'] ?? null;
                                    // $std_marks = $marks['marks'];
                                    $session_year = $marks['session_yr'];
                                    $attendence_id = $marks['attendence_id'];
                                    $marks_id = $marks['marks_id']?? null;
                                    $reg_no = $marks['reg_no'];
                                    $inst_id = $marks['inst_id'];
                                    $course_id = $marks['course_id'];
                                    $paper_id = $marks['paper_id'];
                                    $semester = $marks['semester'];
                                    $paper_type = $marks['paper_type'];
                                    $entry_type = $marks['entry_type'];
                                    $viva_marks = $marks['theory_viva_marks'] ;
                                    $class_test_marks = $marks['theory_test_marks'];
                                    $std_marks = $marks['internal_marks'];

                                    $exam_yr = date('Y');
                                    $row = MarksEntry::find($marks_id);
                                //    echo $marks_id;
                                    if ($row) {
                                        if ($row->paper_type == 1) {
                                            if ($row->paper_entry_type == 1) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_attendance_marks' => $attendance_marks,
                                                    'internal_viva_marks' => $viva_marks,
                                                    'internal_class_test_marks' => $class_test_marks,
                                                    'is_lock' => 0
                                                    
                                                ]);
                                            } elseif ($row->paper_entry_type == 2) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'is_lock' => 0
                                                ]);
                                            }
                                        } elseif ($row->paper_type == 2) {
                                            if ($row->paper_entry_type == 1) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_attendance_marks' => $attendance_marks,
                                                    'internal_viva_marks' => $viva_marks,
                                                    'internal_class_test_marks' => $class_test_marks,
                                                    'is_lock' => 0
                                                ]);
                                            } elseif ($row->paper_entry_type == 2) {
                                               
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_viva_marks' => $viva_marks, // before examiner viva
                                                    'internal_class_test_marks' => $class_test_marks,
                                                    'is_lock' => 0 // assignment on the day of viva
                                                ]);
                                        
                                            }
                                        }
                                    }
                                    else {
                                       
                                        MarksEntry::create([
                                            'inst_id' => $inst_id,
                                            'course_id' => $course_id,
                                            'paper_id' => $paper_id,
                                            'semester' => $semester,
                                            'stud_reg_no' => $reg_no,
                                            'session_yr' => $session_year,
                                            'exam_yr' => $exam_yr,
                                            'paper_type' => $paper_type,
                                            'paper_entry_type' => $entry_type,
                                            'marks' => $std_marks,
                                            'internal_attendance_marks' => $attendance_marks,
                                            'internal_viva_marks' => $viva_marks,
                                            'internal_class_test_marks' => $class_test_marks,
                                            'att_id' => $attendence_id,
                                            'created_on' => now(),
                                            'updated_on' => now(),
                                            'created_by' => $user_id,
                                            'updated_by' => $user_id,
                                            'is_lock' => 0
                                        ]);
                                    }

                                    // update audit trail
                                    $paperType = $paper_type == 1 ? 'Theory' : 'Sessional';
                                    $subjectEntryType = $entry_type == 1 ? 'Internal' : 'External';

                                    auditTrail($user_id, "Marks of {$reg_no} for {$paperType} on {$subjectEntryType} updated to {$std_marks}");
                                }
                            }

                            DB::commit();
                    
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Marks Updated Successfully',
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
                            DB::rollback();
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  $e->getMessage()
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
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

    public function marksFinalSubmit(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('marks-final-submit', $url_data)) {
                        $marks_list = $request->all();

                        try {
                            DB::beginTransaction();

                            foreach ($marks_list as $marks) {
                                $validated = Validator::make($marks, [
                                    'attendence_id' => ['required'],
                                    'marks_id' => ['nullable'],
                                    'reg_no' => ['required'],
                                    'inst_id' => ['required'],
                                    'course_id' => ['required'],
                                    'semester' => ['required'],
                                    'session_yr' => ['required'],
                                    'paper_id' => ['required'],
                                    'paper_type' => ['required'],
                                    'entry_type' => ['required'],
                                    'marks' => ['nullable'],
                                    'is_final' => ['required'],
                                    'attendance_marks' => ['nullable'],
                                    'theory_viva_marks' => ['nullable'],
                                    'theory_test_marks' => ['nullable']
                                ]);

                                if ($validated->fails()) {
                                    return response()->json([
                                        'error' => true,
                                        'message' => $validated->errors()
                                    ]);
                                } else {
                                    $is_final = $marks['is_final'];
                                    $session_year = $marks['session_yr'];
                                    $attendence_id = $marks['attendence_id'];
                                    $marks_id = $marks['marks_id'];
                                    $reg_no = $marks['reg_no'];
                                    $inst_id = $marks['inst_id'];
                                    $course_id = $marks['course_id'];
                                    $paper_id = $marks['paper_id'];
                                    $semester = $marks['semester'];
                                    $paper_type = $marks['paper_type'];
                                    $entry_type = $marks['entry_type'];
                                    $attendance_marks = $marks['attendance_marks'];
                                    $std_marks = $marks['marks'];
                                    $viva_marks = $marks['theory_viva_marks'];
                                    $class_test_marks = $marks['theory_test_marks'];
                                    $std_marks =  $marks['marks'];
                                    
                                    $exam_yr = date('Y');
                                   
                                    $row = MarksEntry::find($marks_id);
                                    if ($row) {
                                        if ($row->paper_type == 1) {
                                            if ($row->paper_entry_type == 1) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_attendance_marks' => $attendance_marks,
                                                    'internal_viva_marks' => $viva_marks,
                                                    'internal_class_test_marks' => $class_test_marks,
                                                    'is_final_submit' => $is_final,
                                                    'is_lock' => 0
                                                ]);
                                            } elseif ($row->paper_entry_type == 2) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'is_final_submit' => $is_final,
                                                    'is_lock' => 0
                                                ]);
                                            }
                                        } elseif ($row->paper_type == 2) {
                                            if ($row->paper_entry_type == 1) {
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_attendance_marks' => $attendance_marks,
                                                    'internal_viva_marks' => $viva_marks,
                                                    'internal_class_test_marks' => $class_test_marks,
                                                    'is_final_submit' => $is_final,
                                                    'is_lock' => 0
                                                ]);
                                            } elseif ($row->paper_entry_type == 2) {
                                               
                                                $row->update([
                                                    'marks' => $std_marks,
                                                    'internal_viva_marks' => $viva_marks, // before examiner viva
                                                    'internal_class_test_marks' => $class_test_marks ,// assignment on the day of viva
                                                    'is_final_submit' => $is_final,
                                                    'is_lock' => 0
                                                ]);
                                        
                                            }
                                        }
                                    } else {
                                        MarksEntry::create([
                                            'inst_id' => $inst_id,
                                            'course_id' => $course_id,
                                            'paper_id' => $paper_id,
                                            'semester' => $semester,
                                            'stud_reg_no' => $reg_no,
                                            'session_yr' => $session_year,
                                            'exam_yr' => $exam_yr,
                                            'paper_type' => $paper_type,
                                            'paper_entry_type' => $entry_type,
                                            'marks' => $std_marks,
                                            'internal_attendance_marks' => $attendance_marks,
                                            'internal_viva_marks' => $viva_marks,
                                            'internal_class_test_marks' => $class_test_marks,
                                            'is_final_submit' => $is_final,
                                            'att_id' => $attendence_id,
                                            'created_on' => now(),
                                            'updated_on' => now(),
                                            'created_by' => $user_id,
                                            'updated_by' => $user_id,
                                            'is_lock' => 0
                                        ]);
                                    }
                              

                                    // update audit trail
                                    $paperType = $paper_type == 1 ? 'Theory' : 'Sessional';
                                    $subjectEntryType = $entry_type == 1 ? 'Internal' : 'External';

                                    auditTrail($user_id, "Marks of {$reg_no} for {$paperType} on {$subjectEntryType} updated to {$std_marks} and finally submitted");
                                }
                            }

                        //    }

                            

                            DB::commit();

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Marks Finally Submitted',
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
                            DB::rollback();
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  $e->getMessage()
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
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

    public function marksPdf(Request $request)
    {
        $session_yr = $request->session_yr;
        $semester = 'SEMESTER_I';
        $inst_id = $request->inst_id;
        $course_id = $request->course_id;
        $paper_id = $request->paper_id;
        $paper_type = $request->paper_type;
        $subject_entry_type = $request->entry_type;
        $examiner_name = $request->user_fullname;

        $marks_list = MarksEntry::where(['session_yr' => $session_yr, 'semester' => $semester, 'inst_id' => $inst_id, 'course_id' => $course_id, 'paper_id' => $paper_id, 'paper_type' => $paper_type, 'paper_entry_type' => $subject_entry_type])
            ->with('paperMarks:paper_id_pk,paper_name,paper_full_marks,paper_internal_marks,paper_external_marks,paper_sessional_internal_viva_marks,paper_sessional_internal_class_test_marks,paper_sessional_internal_attendance_marks,paper_internal_attendance_marks,paper_internal_theory_class_test_marks,paper_internal_theory_viva_marks,paper_sess_assign_viva_marks,paper_sess_before_viva_marks', 'student:student_reg_no,student_fullname', 'institute:inst_sl_pk,institute_name', 'course:course_id_pk,course_name', 'user:u_id,u_fullname')
            ->orderBy('id', 'asc')
             ->get();
            //  dd($marks_list);
        $finalList = $marks_list->map(function ($single, $key) {
            $attendanceMarks = $single->internal_attendance_marks ?? '0';
            $classTestMarks = $single->internal_class_test_marks ?? '0';
            $vivaMarks = $single->internal_viva_marks ?? '0';
            $totalMarks = '0';
            if ($attendanceMarks === 'AB' || $classTestMarks === 'AB' || $vivaMarks === 'AB') {
                $totalMarks = 'AB'; 
            } elseif ($attendanceMarks === 'RA' || $classTestMarks === 'RA' || $vivaMarks === 'RA') {
                $totalMarks = 'RA'; 
            } else {
                $totalMarks = (int)$attendanceMarks + (int)$classTestMarks + (int)$vivaMarks;
            }
            return [
                'sl_no' => $key + 1,
                'student_reg_no' => $single->student->student_reg_no,
                'student_name' => $single->student->student_fullname,
                'student_marks_obtained' => $single->marks,
                'inst_name' => $single->institute->institute_name,
                'course_name' => $single->course->course_name,
                'semester' => $single->semester,
                'paper' => $single->paperMarks->paper_name,
                'paper_internal_attendance_marks' => $single->paperMarks->paper_internal_attendance_marks,
                'paper_internal_theory_class_test_marks' => $single->paperMarks->paper_internal_theory_class_test_marks,
                'paper_internal_theory_viva_marks' => $single->paperMarks->paper_internal_theory_viva_marks,
                'paper_sessional_internal_attendance_marks' => $single->paperMarks->paper_sessional_internal_attendance_marks,
                'paper_sessional_internal_class_test_marks' => $single->paperMarks->paper_sessional_internal_class_test_marks,
                'paper_sessional_internal_viva_marks' => $single->paperMarks->paper_sessional_internal_viva_marks,
                'paper_sess_assign_viva_marks' =>$single->paperMarks->paper_sess_assign_viva_marks,
                'paper_sess_before_viva_marks' =>$single->paperMarks->paper_sess_before_viva_marks,
                'paper_entry_type' => $single->paper_entry_type == '1' ? 'Internal' : 'External',
                'paper_type' => $single->paper_type == '1' ? 'Theory' : 'Sessional',
                'internal_attendance_marks' => $attendanceMarks,
                'internal_viva_marks' => $vivaMarks,
                'internal_class_test_marks' => $classTestMarks,
               
                'total_marks' =>  $totalMarks,
                'u_fullname' => $single->user->u_fullname
            ];
        });
        $paper_marks = Paper::where('paper_id_pk', $paper_id)
            ->where('paper_semester', $semester)
            ->where('paper_category', $paper_type)
            ->where('inst_id', $inst_id)
            ->where('course_id', $course_id)
            ->where('paper_affiliation_year', $session_yr)
            ->where('is_active', 1)
            ->first();
        $marks = null;
        $internal_attendance_marks = null;
        $full_marks = null;
        if ($paper_marks) {
            if ($paper_type == 1) {
                if ($subject_entry_type == 1) {
                    $marks = $paper_marks->paper_internal_marks;
                     $internal_attendance_marks = $paper_marks->paper_internal_attendance_marks;
                     $full_marks= $paper_marks->paper_full_marks;
                } else {
                    $marks = $paper_marks->paper_external_marks;
                    $full_marks= $paper_marks->paper_full_marks;
                }
            } elseif ($paper_type == 2) {
                if ($subject_entry_type == 1) {
                    $marks = $paper_marks->paper_internal_marks;
                    // $internal_attendance_marks = $paper_marks->paper_internal_attendance_marks;
                    $full_marks= $paper_marks->paper_full_marks;
                } else {
                    $marks = $paper_marks->paper_external_marks;
                    $full_marks= $paper_marks->paper_full_marks;
                }
            }
        }
       
            $pdf = Pdf::loadView('exports.marks', [
                'marks' => $marks,
                'students' => $finalList,
                'internal_attendance_marks' => $internal_attendance_marks,
                'full_marks'=>$full_marks,
                'paper_type' =>$paper_type,
                'subject_entry_type'=>$subject_entry_type,
                'examiner_name' => $examiner_name,
            ]);
     
        return $pdf->setPaper('a4', 'portrait')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('marks.pdf');
    }
   
    public function marksLock(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))
                                ->where('t_expired_on', '>=', $now)
                                ->first();
            
            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
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
                    if (in_array('marks-lock', $url_data)) {
                        $validated = Validator::make($request->all(), [
                            'student_reg_no' => ['required'],  
                            'paper_id' => ['required'],
                            'subject_entry_type' => ['required'],
                            ''
                           
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        try {
                            DB::beginTransaction();
                             $student_reg_no = $request->student_reg_no; 
                            $subject_entry_type = $request->subject_entry_type;
                            $paper_id = $request->paper_id;
                            $row = MarksEntry::where('stud_reg_no', $student_reg_no)
                            ->where('paper_entry_type', $subject_entry_type)
                            ->where('paper_id', $paper_id)
                            ->where('is_final_submit', 1)
                            ->where('semester', 'SEMESTER_I')
                            ->first();;  

                            if ($row) {
                                $row->update([
                                    'is_lock' => 1,  
                                    'is_final_submit' => 0  
                                ]);
                            } else {
                                return response()->json([
                                    'error' => true,
                                    'message' => 'Marks entry not found.'
                                ], 404);
                            }
                            auditTrail($user_id, "Marks entry Final Submit Unlocked for student reg: {$student_reg_no}");

                            DB::commit();
                            return response()->json([
                                'error' => false,
                                'message' => 'Marks entry unlocked successfully.'
                            ], 200);

                        } catch (Exception $e) {
                            DB::rollback();
                            return response()->json([
                                'error' => true,
                                'message' => $e->getMessage()
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => "Oops! You don't have sufficient permission."
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => "Oops! You don't have sufficient permission."
                    ], 401);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Unable to process your request due to invalid token.'
                ], 401);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Unable to process your request due to non-availability of token.'
            ], 401);
        }
    }
   

}
