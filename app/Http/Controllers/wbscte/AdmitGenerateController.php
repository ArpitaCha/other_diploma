<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Token;
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

class AdmitGenerateController extends Controller
{
    
    public function list(Request $request, $session_year, $inst, $course)
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

                    if (in_array('student-list', $url_data)) { 
                        $students = Student::where([
                            'student_session_yr' => $session_year,
                            'student_inst_id' => $inst,
                            'student_course_id' => $course,
                            'student_is_enrolled' => 1,
                            'student_exam_fees_status' => 1
                        ])->with('roll')->get()
                            ->map(function ($value) {
                                return [
                                    'reg_no' => $value->student_reg_no,
                                    'student_name' => $value->student_fullname,
                                    'parent_name' => $value->student_guardian_name,
                                    'reg_year' => $value->student_reg_year,
                                    'roll_no' => $value->roll->roll_no,
                                    'is_verified' => true,
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
    public function downloadAdmitAll(Request $request, $session_year, $inst, $course)
    {
        $now    =   date('Y-m-d H:i:s');
        $schedule =  CnfgMarks::where('config_for', 'ADMIT_CARD_DOWNLOAD_ALL')
                                                        ->where('start_at', '<=', $now)  
                                                        ->where('end_at', '>=', $now)   
                                                        ->where('semester', 'SEMESTER_I')
                                                        ->first();  
                                                      
         
            if(!$schedule){
                return response()->json([
                    'error' => true,
                    'message' => 'Admit Card Download Schedule is not available'
                ]);
            }else{
                $papers = TheorySubject::select('paper_name','paper_code','paper_category')->where('course_id', $course)->where('inst_id',$inst)->where('paper_semester','SEMESTER_I')->get();

                $students = Student::where('student_inst_id', $inst)
                       ->where('student_course_id', $course)
                       ->where('student_session_yr', $session_year)
                       ->where('student_is_enrolled', 1)
                       ->where('student_exam_fees_status', 1)
                       ->where('student_semester','SEMESTER_I')
                       ->with('roll', 'institute', 'course')
                       ->get()
                       ->map(function ($value) use ($papers) {
                           return [
                               'reg_no' => $value->student_reg_no,
                               'student_name' => $value->student_fullname,
                               'parent_name' => $value->student_guardian_name,
                               'reg_year' => $value->student_reg_year,
                               'roll_no' => $value->roll->roll_no,
                               'inst_name' => $value->institute->inst_name,
                               'course_name' => $value->course->course_name,
                               'paper' => $papers, 
                              
                           ];
                       });
                    

                if ($students->isEmpty()) {
                   
                        return response()->json([
                            'error' => true,
                            'message' => 'No Data Found'
                        ]);
                }else{
                    $pdf = Pdf::loadView('admitcard.admitAll', [
                        'students' => $students,
                       
                        'semester' => 'SEMESTER_I',
                        
                        
                    ]);

                    $pdf->setPaper('A4', 'portrait');
                    $pdf->output();
                    $domPdf = $pdf->getDomPDF();
                    $canvas = $domPdf->get_canvas();
                    $canvas->page_text(10, 5, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);

                    return $pdf->setPaper('a4', 'portrait')
                        ->setOption(['defaultFont' => 'sans-serif'])
                        ->stream("download-all.pdf");

                }
        

            }

  
        
    } 
    public function downloadAdmit(Request $request, $reg_no)
    {
        $now    =   date('Y-m-d H:i:s');
        $schedule =  CnfgMarks::where('config_for', 'ADMIT_CARD_DOWNLOAD')
                                                        ->where('start_at', '<=', $now)  
                                                        ->where('end_at', '>=', $now)   
                                                        ->where('semester', 'SEMESTER_I')
                                                        ->first();  
                                                      
         
            if(!$schedule){
                return response()->json([
                    'error' => true,
                    'message' => 'Admit Card Download Schedule is not available'
                ]);
            }else{
                $student = Student::where('student_reg_no', $reg_no)
                ->where('student_is_enrolled', 1)
                ->where('student_exam_fees_status', 1)
                ->where('student_semester','SEMESTER_I')
                ->with('roll', 'institute', 'course')
                ->first();
                if ($student == null) {
                   
                    return response()->json([
                        'error' => true,
                        'message' => 'No Data Found'
                    ]);
                }
                $papers = TheorySubject::where('course_id', $student->student_course_id)
                ->where('inst_id', $student->student_inst_id)
                ->where('paper_semester','SEMESTER_I')
                ->get();
                $data = [
                    'reg_no' => $student->student_reg_no,
                    'student_name' => $student->student_fullname,
                    'parent_name' => $student->student_guardian_name,
                    'reg_year' => $student->student_reg_year,
                    'roll_no' => $student->roll->roll_no,
                    'inst_name' => $student->institute->inst_name,
                    'course_name' => $student->course->course_name,
                    'paper' => $papers
                ];
                
                    $pdf = Pdf::loadView('admitcard.admit', 
                        [
                            'student' => $data,
                            'semester' => 'SEMESTER_I',
                        
                        
                    ]);

                    $pdf->setPaper('A4', 'portrait');
                    $pdf->output();
                    $domPdf = $pdf->getDomPDF();
                    $canvas = $domPdf->get_canvas();
                    $canvas->page_text(10, 5, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);

                    return $pdf->setPaper('a4', 'portrait')
                        ->setOption(['defaultFont' => 'sans-serif'])
                        ->stream("admit.pdf");

            }
        

         

  
        
    }    
}