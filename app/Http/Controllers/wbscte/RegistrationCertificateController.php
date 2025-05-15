<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;use App\Models\wbscte\Token;
use App\Models\wbscte\User;
use App\Models\wbscte\Student;
use App\Models\wbscte\Institute;
use App\Models\wbscte\CnfgMarks;
use App\Models\wbscte\ExamRoll;
use App\Models\wbscte\TheorySubject;

use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Validator;
use DB;

class RegistrationCertificateController extends Controller
{
    public function list(Request $request,$session_year, $inst, $course)
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

                    if (in_array('register-student-list', $url_data)) { 
                        $students = Student::where([
                            'student_session_yr' => $session_year,
                            'student_inst_id' => $inst,
                            'student_course_id' => $course,
                            'student_status_s1' => 6,
                        ])->get()
                            ->map(function ($value) {
                                return [
                                    'reg_no' => $value->student_reg_no,
                                    'student_name' => $value->student_fullname,
                                    'parent_name' => $value->student_guardian_name,
                                    'reg_year' => $value->student_reg_year,
                                   
                                ];
                            });
                            if ($students->count()) {
                                return response()->json([
                                    'error'         =>  false,
                                    'message'       =>  'List found',
                                    'list'  =>  $students
                                ]);
                            } else {
                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  'No Data available'
                                ]);
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
    public function downloadPdf(Request $request,$reg_no,$type)
    {
                    $student = Student::where('student_reg_no', $reg_no)
                ->where('student_status_s1', 6)
                ->with('institute', 'course')
                ->first();

            if ($student == null) {
                return response()->json([
                    'error' => true,
                    'message' => 'No Data Found'
                ]);
            }

            // Prepare data for PDF
            $data = [
                'reg_no' => $student->student_reg_no,
                'student_name' => $student->student_fullname,
                'parent_name' => $student->student_guardian_name,
                'reg_year' => $student->student_reg_year,
                'course_name' => $student->course->course_name,
            ];

            $pdf = Pdf::loadView('exports.registration-certificate', [
                'student' => $data,
            ]);
            $pdf->setPaper('A4', 'portrait');
            $pdf->output();
            $domPdf = $pdf->getDomPDF();
            $canvas = $domPdf->get_canvas();
            $canvas->page_text(10, 5, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);
            if ($type == 4) {
                if ($student->is_reg_print == 0) {
                    $student->is_reg_print = 1;
                    $student->save(); // Mark as printed
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Already Printed'
                    ]);
                }
            }
            return $pdf->setPaper('a4', 'portrait')
                ->setOption(['defaultFont' => 'sans-serif'])
                ->stream("registration.pdf");

                }

}
