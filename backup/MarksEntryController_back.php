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
                            'semester' => ['required'],
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
                            $semester = $request->semester;
                            $inst_id = $request->inst_id;
                            $course_id = $request->course_id;
                            $paper_id = $request->paper_id;
                            $paper_type = $request->paper_type;
                            $subject_entry_type = $request->entry_type;

                            $marks_list = Attendance::where([
                                'attr_sessional_yr' => $session_yr,
                                'att_sem' => $semester,
                                'att_inst_id' => $inst_id, 
                                'att_course_id' => $course_id,
                                'att_paper_id' => $paper_id, 
                                'att_paper_type' => $paper_type,
                                'att_paper_entry_type' => $subject_entry_type,
                            ]);

                                if($marks_list ->where('is_final_submit',1)->count()){
                                    $paper_marks = Paper::where('paper_id_pk', $paper_id)
                                    ->where('paper_semester', $semester)
                                    ->where('paper_category', $paper_type)
                                    ->where('inst_id', $inst_id)
                                    ->where('course_id', $course_id)
                                    ->where('paper_affiliation_year', $session_yr)
                                    ->where('is_active', 1)
                                    ->first();
    
                                    if ($paper_marks) {
                                        if ($paper_type == 1) {
                                            $max_marks = [
                                                'internel_marks' => $paper_marks->paper_internal_marks,
                                                'enternel_marks' => $paper_marks->paper_external_marks,
                                                'internal_attendance_marks' => $paper_marks->paper_internal_attendance_marks,
                                            ];
                                        } elseif ($paper_type == 2) {
                                            $max_marks = [
                                                'internel_marks' => $paper_marks->paper_sessional_theory_marks,
                                                'enternel_marks' => $paper_marks->paper_sessional_practical_marks,
                                                'internal_attendance_marks' => $paper_marks->paper_internal_attendance_marks,
                                            ];
                                        }
                                    }

                                    $marks_list = $marks_list->with('marks:id,att_id,marks', 'paperMarks:paper_id_pk,paper_full_marks,paper_internal_marks,paper_external_marks,paper_sessional_theory_marks,paper_sessional_practical_marks,paper_internal_attendance_marks', 'student:student_reg_no,student_fullname')
                                    ->orderBy('att_id', 'asc')
                                    ->get();

                                    // return $marks_list;
                                    
                                    

                                    if (sizeof($marks_list) > 0) {
                                        $reponse = array(
                                            'error'         =>  false,
                                            'message'       =>  'Data found',
                                            'marks'   =>  MarksListResource::collection($marks_list),
                                            'max_marks' => $max_marks
                                        );
                                        return response(json_encode($reponse), 200);
                                    } else {
                                        $reponse = array(
                                            'error'     =>  true,
                                            'message'   =>  'No Data available'
                                        );
                                        return response(json_encode($reponse), 404);
                                    }
                                }
                                else {

                                    $reponse = array(
                                        'error'     =>  true,
                                        'message'   =>  'Attendence not submitted finally'
                                    );
                                    return response(json_encode($reponse), 404);
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
                                    'marks' => ['required'],
                                ]);

                                if ($validated->fails()) {
                                    return response()->json([
                                        'error' => true,
                                        'message' => $validated->errors()
                                    ]);
                                } else {
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
                                    $marks = $marks['marks'];
                                    $exam_yr = date('Y');

                                    $row = MarksEntry::find($marks_id);

                                    if ($row) {
                                        $row->update([
                                            'marks' => $marks,
                                        ]);
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
                                            'marks' => $marks,
                                            'att_id' => $attendence_id,
                                            'created_on' => now(),
                                            'updated_on' => now(),
                                            'created_by' => $user_id,
                                            'updated_by' => $user_id
                                        ]);
                                    }


                                    // update audit trail
                                    $paperType = $paper_type == 1 ? 'Theory' : 'Sessional';
                                    $subjectEntryType = $entry_type == 1 ? 'Internal' : 'External';

                                    auditTrail($user_id, "Marks of {$reg_no} for {$paperType} on {$subjectEntryType} updated to {$marks}");
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
                                    'marks' => ['required'],
                                    'is_final' => ['required'],
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
                                    $marks = $marks['marks'];
                                    $exam_yr = date('Y');

                                    $row = MarksEntry::find($marks_id);

                                    if ($row) {
                                        $row->update([
                                            'marks' => $marks,
                                            'is_final_submit' => $is_final
                                        ]);
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
                                            'marks' => $marks,
                                            'is_final_submit' => $is_final,
                                            'att_id' => $attendence_id,
                                            'created_on' => now(),
                                            'updated_on' => now(),
                                            'created_by' => $user_id,
                                            'updated_by' => $user_id
                                        ]);
                                    }

                                    // update audit trail
                                    $paperType = $paper_type == 1 ? 'Theory' : 'Sessional';
                                    $subjectEntryType = $entry_type == 1 ? 'Internal' : 'External';

                                    auditTrail($user_id, "Marks of {$reg_no} for {$paperType} on {$subjectEntryType} updated to {$marks} and finally submitted");
                                }
                            }

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
        $semester = $request->semester;
        $inst_id = $request->inst_id;
        $course_id = $request->course_id;
        $paper_id = $request->paper_id;
        $paper_type = $request->paper_type;
        $subject_entry_type = $request->entry_type;

        $marks_list = MarksEntry::where(['session_yr' => $session_yr, 'semester' => $semester, 'inst_id' => $inst_id, 'course_id' => $course_id, 'paper_id' => $paper_id, 'paper_type' => $paper_type, 'paper_entry_type' => $subject_entry_type])
            ->with('paperMarks:paper_id_pk,paper_name,paper_full_marks,paper_internal_marks,paper_external_marks,paper_sessional_theory_marks,paper_sessional_practical_marks', 'student:student_reg_no,student_fullname', 'institute:inst_sl_pk,institute_name', 'course:course_id_pk,course_name')
            ->orderBy('id', 'asc')
            ->get();
        $finalList = $marks_list->map(function ($single, $key) {

            return [
                'sl_no' => $key + 1,
                'student_reg_no' => $single->student->student_reg_no,
                'student_name' => $single->student->student_fullname,
                'student_marks_obtained' => $single->marks,
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
        if ($paper_marks) {
            if ($paper_type == 1) {
                if ($subject_entry_type == 1) {
                    $marks = $paper_marks->paper_internal_marks;
                } else {
                    $marks = $paper_marks->paper_external_marks;
                }
            } elseif ($paper_type == 2) {
                if ($subject_entry_type == 1) {
                    $marks = $paper_marks->paper_sessional_theory_marks;
                } else {
                    $marks = $paper_marks->paper_sessional_practical_marks;
                }
            }
        }
        //return $marks_list;
        $pdf = Pdf::loadView('exports.marks', [
            'marks' => $marks,
            'students' => $finalList
        ]);
        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('marks.pdf');
    }
}
