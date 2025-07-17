<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;

use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Attendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\TheorySubject;
use App\Models\wbscte\Enrollment;
use App\Models\wbscte\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\wbscte\AttendanceResource;
use App\Http\Resources\wbscte\InternalExaminerResource;
use App\Models\wbscte\MarksEntry;

class AttendanceController extends Controller
{
    //Attendance List Api
    public function list(Request $request)
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

                    if (in_array('attendance-list', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'course_id' => ['required'],
                            'paper_category' => ['required'],
                            'session_yr' => ['required'],
                            'paper_id' => ['required'],
                            'subject_entry_type' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $session_yr = $request->session_yr;
                        $inst_id = $request->inst_id;
                        $course_id = $request->course_id;
                        $paper_id = $request->paper_id;
                        $subject_entry_type = $request->subject_entry_type;
                        $paper_category = $request->paper_category;
                        $semester = 'SEMESTER_I';
                        $paperDetails = TheorySubject::find($paper_id);
                        $enrolled_students = Enrollment::where([
                            'inst_id' => $inst_id,
                            'course_id' => $course_id,
                            'semester' => $semester,
                            'academic_year' => $session_yr,
                            'is_enrolled' => 1,
                            'is_paid' => 1,
                            // 'is_nodal_approved' => 1,
                        ])->count();

                        if (!$enrolled_students) {
                            return response()->json([
                                'error' => true,
                                'message' => 'No Student Enrolled'
                            ]);
                        }
                        $attend = Attendance::query();
                        $studentList = $attend->clone()->where(['attr_sessional_yr' => $session_yr, 'att_sem' => $semester, 'att_inst_id' => $inst_id, 'att_course_id' => $course_id, 'att_paper_id' => $paper_id, 'att_paper_type' => $paper_category, 'att_paper_entry_type' => $subject_entry_type])->with('student:student_reg_no,student_fullname')->orderBy('att_reg_no', 'asc')->get();
                        //return $studentList;

                        $present_count = $attend->clone()->where('att_is_present', 1)
                            ->where('att_paper_id', $paper_id)
                            ->where('att_paper_type', $paper_category)
                            ->where('att_paper_entry_type', $subject_entry_type)
                            ->count();

                        $absent_count = $attend->clone()->where('att_is_absent', 1)
                            ->where('att_paper_id', $paper_id)
                            ->where('att_paper_type', $paper_category)
                            ->where('att_paper_entry_type', $subject_entry_type)
                            ->count();

                        $ra_count = $attend->clone()->where('att_is_ra', 1)
                            ->where('att_paper_id', $paper_id)
                            ->where('att_paper_type', $paper_category)
                            ->where('att_paper_entry_type', $subject_entry_type)
                            ->count();

                        if (sizeof($studentList) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'student'   =>  AttendanceResource::collection($studentList),
                                'paper' => $paperDetails->paper_name,
                                'enrolled_count' => sizeof($studentList),
                                'present_count' => $present_count,
                                'absent_count' => $absent_count,
                                'ra_count' => $ra_count
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No Data available'
                            );
                            return response(json_encode($reponse), 200);
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

    //Individual Attendance
    public function individualAttendance(Request $request)
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

                    if (in_array('individual-attendance', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'student_id' => ['required'],
                            'student_reg_no' => ['required'],
                            'inst_id' => ['required'],
                            'course_id' => ['required'],
                            'paper_category' => ['required'], //Theory or Sessional 1 for theory 2 for Sessional
                            'session_yr' => ['required'],
                            'paper_id' => ['required'],
                            'subject_entry_type' => ['required'], //1 for Internal 2 for External
                            'is_present' => ['required'],
                            'is_absent' => ['required'],
                            'is_ra' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $student_id = $request->student_id;
                        $student_reg_no = $request->student_reg_no;
                        $present = $request->is_present;
                        $absent = $request->is_absent;
                        $ra = $request->is_ra;
                        $semester = 'SEMESTER_I';
                        $paper_category = $request->paper_category;
                        $subject_entry_type = $request->subject_entry_type;
                        try {
                            $attend = Attendance::find($student_id);
                            if ($attend) {
                                DB::beginTransaction();
                                if (!$present && !$absent && !$ra) {
                                    $present = true;
                                }
                                $attend->update([
                                    'att_is_present' => $present,
                                    'att_is_absent' => $absent,
                                    'att_is_ra' => $ra,
                                    'att_modified_on' => $now,
                                    'att_modified_by' => $user_id
                                ]);
                                $status = '';
                                if ($present) {
                                    $status = 'Present';
                                } else if ($absent) {
                                    $status = 'Absent';
                                } else if ($ra) {
                                    $status = 'RA';
                                }
                                $paperType = $paper_category == 1 ? 'Theory' : 'Sessional';
                                $subjectEntryType = $subject_entry_type == 1 ? 'Internal' : 'External';
                                auditTrail($user_id, "Attendance of {$student_reg_no} for {$paperType} on {$subjectEntryType} updated to  {$status}");
                                DB::commit();
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  "Attendance updated successfully",
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'         =>  true,
                                    'message'       =>  "Attendance not found",
                                );
                                return response(json_encode($reponse), 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'message' => $e->getMessage()
                                )
                            );
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

    //Final submit (Bulk) Attendance
    public function finalAttendanceSubmit(Request $request)
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

                    if (in_array('attendance-final-submit', $url_data)) { //check url has permission or not
                        $all_attends = $request->all();

                        try {
                            foreach ($all_attends as $key => $single_attend) {
                                $validated = Validator::make($single_attend, [
                                    'student_id' => ['required'],
                                    'student_reg_no' => ['required'],
                                    'inst_id' => ['required'],
                                    'course_id' => ['required'],
                                    'paper_category' => ['required'], //Theory or Sessional 1 for theory 2 for Sessional
                                    'session_yr' => ['required'],
                                    'paper_id' => ['required'],
                                    'subject_entry_type' => ['required'], //1 for Internal 2 for External
                                    'is_present' => ['required'],
                                    'is_absent' => ['required'],
                                    'is_ra' => ['required'],
                                    'is_final' => ['required']
                                ]);

                                if ($validated->fails()) {
                                    return response()->json([
                                        'error' => true,
                                        'message' => $validated->errors()
                                    ]);
                                } else {

                                    $student_id = $single_attend['student_id'];
                                    $student_reg_no = $single_attend['student_reg_no'];
                                    $paper_category = $single_attend['paper_category'];
                                    $subject_entry_type = $single_attend['subject_entry_type'];
                                    $is_final = $single_attend['is_final'];

                                    DB::beginTransaction();

                                    $attend = Attendance::find($student_id);

                                    if ($attend) {
                                        $attend->update([
                                            'is_final_submit' => $is_final,
                                            'att_modified_on' => $now,
                                            'att_modified_by' => $user_id,
                                            'is_lock' => 0,
                                        ]);

                                        $paperType = $paper_category == 1 ? 'Theory' : 'Sessional';
                                        $subjectEntryType = $subject_entry_type == 1 ? 'Internal' : 'External';

                                        auditTrail($user_id, "Attendance of {$student_reg_no} for {$paperType} on {$subjectEntryType} finally submitted");
                                    }

                                    DB::commit();
                                }
                            }

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  "Attendance finally submitted successfully",
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'message' => $e->getMessage()
                                )
                            );
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
    public function attendanceLock(Request $request)
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
                            'subject_entry_type' => ['required'], //1 for Internal 2 for External
                            'paper_id' => ['required'],
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
                            $row = Attendance::where('att_reg_no', $student_reg_no)
                                ->where('att_paper_entry_type', $subject_entry_type)
                                ->where('att_paper_id', $paper_id)
                                ->where('is_final_submit', 1)
                                ->where('att_sem', 'SEMESTER_I')
                                ->first();
                            // dd($row);

                            if (!$row) {
                                DB::rollBack();
                                return response()->json([
                                    'error' => true,
                                    'message' => 'No data found.'
                                ], 200);
                            } else {
                                $updated = $row->update([
                                    'is_lock' => 1,
                                    'is_final_submit' => 0
                                ]);

                                if ($updated) {

                                    $marksEntry = MarksEntry::where('att_id', $row->att_id)->first();

                                    if ($marksEntry) {
                                        $marksEntry->update([
                                            "marks" => NULL,
                                            "internal_attendance_marks" => NULL,
                                            "internal_viva_marks" => NULL,
                                            "internal_class_test_marks" => NULL,
                                        ]);
                                    } else {
                                        generateLaravelLog("Marks entry not found for att_id: {$row->att_id}");
                                    }

                                    auditTrail($user_id, "Attendance Final Submit Unlocked for student reg: {$student_reg_no}");
                                }

                                DB::commit();

                                return response()->json([
                                    'error' => false,
                                    'message' => 'Attendance has been unlocked successfully.'
                                ], 200);
                            }
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
    public function attendanceSheet(Request $request)
    {
        $request->validate([
            'academic_year' => ['required'],
            'inst_id' => ['required'],
            'course_id' => ['required'],
            'semester' => ['required'],
            'subject_entry_type' => ['required'],
            'paper_category' => ['required'],
            'paper_id' => ['required'],
        ]);

        $inst_id = $request->inst_id;
        $course_id = $request->course_id;
        $academic_year = $request->session_yr;
        $semester = 'SEMESTER_I';
        $paper_id = $request->paper_id;

        $course = Course::where('course_id_pk', $course_id)
            ->where('is_active', 1)
            ->first();

        if (!$course) {
            return response()->json([
                'error' => true,
                'message' => 'Course not found'
            ]);
        }

        $course_code = $course->course_code;
        $course_name = $course->course_name;

        $paperDetails = TheorySubject::select('paper_code', 'paper_name')
            ->where('paper_id_pk', $paper_id)
            ->first();

        if (!$paperDetails) {
            return response()->json([
                'error' => true,
                'message' => 'Paper not found'
            ]);
        }

        $paper_code = $paperDetails->paper_code;
        $paper_name = $paperDetails->paper_name;

        $reg_nos = Enrollment::where([
            'inst_id' => $inst_id,
            'course_id' => $course_id,
            'semester' => $semester,
            'academic_year' => $academic_year,
            'is_enrolled' => 1,
            'is_paid' => 1,
        ])
            ->where('enrolled_paper', 'LIKE', "%{$paper_code}%")
            ->pluck('reg_no')
            ->unique()
            ->filter()
            ->toArray();

        if (empty($reg_nos)) {
            return response()->json([
                'error' => true,
                'message' => "No students enrolled in paper {$paper_code}"
            ]);
        }

        $student_list = Student::whereIn('student_reg_no', $reg_nos)
            ->where('student_inst_id', $inst_id)
            ->select('student_reg_no', 'student_fullname')
            ->get()
            ->map(function ($student) {
                return [
                    'reg_no' => $student->student_reg_no,
                    'name' => $student->student_fullname,
                ];
            })
            ->toArray();

        if (empty($student_list)) {
            return response()->json([
                'error' => true,
                'message' => 'No valid student records found'
            ]);
        }

        // 🧾 PDF Export using Laravel DOMPDF
        $pdf = Pdf::loadView('exports.attendence-sheet-pdf', [
            'academic_year' => $academic_year,
            'semester' => $semester,
            'students' => $student_list,
            'course_name' => $course_name,
            'course_code' => $course_code,
            'paper_name' => $paper_name,
            'paper_code' => $paper_code,
            'inst_id' => $inst_id,
        ]);

        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream("attendance-sheet-vtc_{$inst_id}-sem{$semester}-{$academic_year}.pdf");
    }
}
