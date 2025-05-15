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
use App\Models\wbscte\VenueAllocationDetail;
use Illuminate\Support\Facades\DB;
use App\Models\wbscte\User;
use Illuminate\Support\Str;
use App\Models\wbscte\TheorySubject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class VenueAllocationController extends Controller
{
    //
    public function allotedList(Request $request)
    {
        {
            if ($request->header('token')) {
                $now    =   date('Y-m-d H:i:s');
                $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
    
                if ($token_check) {  // check the token is expire or not
                    $validator = Validator::make($request->all(), [
                        //'access_urls' => 'required|array',
                        'session_year'   => 'required',
                        'exam_year'    => 'required',
                        'inst_id'    => 'required',
                        'course_id'       => 'required',
                       
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
                            
    
                            if (in_array('venue-list', $url_data)) { 
                              //check url has permission or not
                                
                                try {
                                   
                                    $inst_id = $request->inst_id;
                                    $course_id = $request->course_id;
                                    // dd($course_id);
                                    $semester = 'SEMESTER_I';
                                    $session_yr = $request->session_year;
                                    $exam_year = $request->exam_year;
                                    $enrolled_students = Enrollment::where('inst_id', $inst_id)
                                                    ->where('course_id', $course_id)
                                                    ->where('session_year', $session_yr)
                                                    ->where('exam_year', $exam_year)
                                                    ->where('is_paid', 1)
                                                    ->with('institute','course', 'venueallocationdetail')
                                                    ->get()
                                                    ->groupBy(function ($student) {
                                                        // Group by venue_id + room_no
                                                        return $student->venueallocationdetail->venue_id . '-' . $student->venueallocationdetail->room_no;
                                                    })
                                                    ->map(function ($group) {
                                                        $first = $group->first(); // pick one record from group
                                                        return [
                                                            'venue_id' => $first->venueallocationdetail->venue_id,
                                                            'venue_name' => $first->venueallocationdetail->venue_name,
                                                            'institute_name' => $first->institute->institute_name,
                                                            'course_name' => $first->course->course_name,
                                                            'room_no' => $first->venueallocationdetail->room_no,
                                                            'student_no' => $first->venueallocationdetail->student_no
                                                        ];
                                                    })
                                                    ->values();
                                                        
  
                                    $student_count = $enrolled_students->count();
                                  
                                    if($student_count > 0)
                                    {
                                        
                                        return response()->json([
                                            'error'     =>  false,
                                            'message'   =>  'Enrollment students list found',
                                            'list'=> $enrolled_students,
                                            'count' => $student_count,
                                        ],200);
                                      

                                    }else{
                                        return response()->json([
                                            'error'     =>  true,
                                            'message'   =>  'Students are not enrolled',
                                        ],200);

                                    }
                                 
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
    public function allotedVenue(Request $request)
    {
            if ($request->header('token')) {
                $now    =   date('Y-m-d H:i:s');
                $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
    
                if ($token_check) {  // check the token is expire or not
                    $validator = Validator::make($request->all(), [
                        //'access_urls' => 'required|array',
                        'session_year'   => 'required',
                        'exam_year'    => 'required',
                        'inst_id'    => 'required',
                        'course_id'       => 'required',
                        'center_id'       => 'required',
                        'center_name'       => 'required',
                        'room_no'       => 'required',
                        'student_no'       => 'required',
                       
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
                            
    
                            if (in_array('alloted-venue', $url_data)) { 
                              //check url has permission or not
                              try {
                                DB::beginTransaction();
                            
                                $inst_id     = $request->inst_id;
                                $course_id   = $request->course_id;
                                $semester    = 'SEMESTER_I';
                                $session_yr  = $request->session_year;
                                $exam_year   = $request->exam_year;
                                $venue_id    = $request->center_id;
                                $student_no  = (int) $request->student_no;
                                $room_no     = $request->room_no;
                                $venue_name  = $request->center_name;
                            
                                $enrolled_students = Enrollment::where([
                                    'inst_id'       => $inst_id,
                                    'course_id'     => $course_id,
                                    'semester'      => $semester,
                                    'academic_year' => $session_yr,
                                    'exam_year'     => $exam_year,
                                    'is_enrolled'   => 1,
                                    'is_paid'       => 1,
                                ])->with('student','institute')->get();
                            
                                if ($enrolled_students->isEmpty()) {
                                    return response()->json([
                                        'error'   => true,
                                        'message' => 'No students enrolled.',
                                    ]);
                                }
                            
                                $student_count = $enrolled_students->count();
                                $already_allotted = VenueAllocationDetail::where([
                                    'inst_id'    => $inst_id,
                                    'course_id'  => $course_id,
                                    'semester'   => $semester,
                                    'session_year' => $session_yr,
                                    // 'exam_year'  => $exam_year,
                                ])->count();
                            
                                // Check if all students are allotted
                                if ($already_allotted >= $student_count) {
                                    return response()->json([
                                        'error'   => true,
                                        'message' => 'All enrolled students are already allotted.',
                                    ]);
                                }
                            
                                if ($student_no <= $student_count) {
                                    VenueAllocationDetail::create([
                                        'student_no'   => $student_no,
                                        'venue_id'     => $venue_id,
                                        'course_id'    => $course_id,
                                        'inst_id'      => $inst_id,
                                        'venue_name'   => $venue_name,
                                        'session_year' => $session_yr,
                                        'semester'     => $semester,
                                        'room_no'      => $room_no 
                                    ]);
                            
                                    DB::commit();
                            
                                    return response()->json([
                                        'error'   => false,
                                        'message' => 'Venue allotted successfully',
                                    ]);
                                } else {
                                    return response()->json([
                                        'error'   => true,
                                        'message' => 'Enrolled student count exceeds the student number.',
                                    ]);
                                }
                            
                            } catch (Exception $e) {
                                DB::rollback();
                                return response()->json([
                                    'error'   => true,
                                    'message' => $e->getMessage()
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
