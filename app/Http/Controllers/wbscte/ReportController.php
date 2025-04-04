<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use App\Models\wbscte\Attendance;

use App\Models\wbscte\MarksEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\wbscte\MarksListResource;
use App\Models\wbscte\Paper;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function StudentWiseReport(Request $request){
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

                    if (in_array('report-generate', $url_data)) {
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'course_id' => ['required'],
                            'semester' => ['required'],
                            'session_yr' => ['required'],
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
                            $type = $request->type;
                                if($type == "attendance"){
                              // Fetch the list of student attendance records
                                        $student_list = Attendance::where('attr_sessional_yr',$session_yr)->where('att_sem',$semester)->where('att_inst_id',$inst_id)->where('att_course_id',$course_id)
                                        ->with('paperMarks:paper_id_pk,paper_code,paper_name', 'student:student_reg_no,student_fullname','institute:inst_sl_pk,institute_code')
                                        ->orderBy('att_reg_no', 'asc')
                                        ->get();
                                        // Process the data to create a single record per student-paper combination
                                        $results = $student_list->groupBy(function($item) {
                                            return $item->att_reg_no . '-' . $item->att_paper_id;
                                        })->map(function($group) {
                                            $data = $group->first();
                                            return [
                                                'registration_no' => $data->att_reg_no,
                                                'student_name' => $data->student->student_fullname,
                                                'semester' => $data->att_sem,
                                                'paper_code' => $data->paperMarks->paper_code,
                                                'paper_name' => $data->paperMarks->paper_name,
                                                'institute_code' =>$data->institute->institute_code,
                                               
                                                'paper' => $group->map(function($item) {
                                                    return [
                                                        'paper_entry_type' => $item->att_paper_entry_type == '1' ? 'Internal':'External',
                                                        'is_final_submit' => $item->is_final_submit == '1' ? 'Yes' : 'No',
                                                        'paper_type' =>$item->att_paper_type == '1' ? 'Theory':'Sessional',
                                                        'attendance_data' => $item->att_is_present ? 'PR' : ($item->att_is_absent ? 'AB' : ($item->att_is_ra ? 'RA' : null)),
                                                        
                                                    ];
                                                })
                                            ];
                                        })->values();

                                }else if($type == "marks_entry"){

                                }
                                if (sizeof($results) > 0) {
                                    return response()->json([
                                        'error' => false,
                                        'message' => 'Data Found',
                                        'results' => $results
                                    ]);
                                } else {
                                    return response()->json([
                                        'error' => true,
                                        'message' => 'No Data Found',
                                    ]);
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
}
