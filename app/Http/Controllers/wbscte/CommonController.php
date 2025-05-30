<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use Illuminate\Support\Arr;
use App\Models\wbscte\State;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use App\Models\wbscte\Course;
use App\Models\wbscte\District;
use App\Models\wbscte\Paper;
use App\Models\wbscte\Student;
use App\Models\wbscte\SessionActive;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Subdivision;
use App\Models\wbscte\Eligibility;
use App\Models\wbscte\CnfgMarks;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\TheorySubject;
use App\Models\wbscte\Venue;
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
use App\Http\Resources\wbscte\SubdivisionResource;
use App\Http\Resources\wbscte\EligibilityResource;
use App\Models\wbscte\OtherDiplomaExaminnerInstitute;


class CommonController extends Controller
{

    public function __construct()
    {
        //$this->auth = new Authentication();
    }
    //State List
    public function allStates(Request $request,$user_type=null)
    {  
        if($user_type=null){
            $allowed_urls = $request->get('allowed_urls', []);
            if (in_array('state-list', $allowed_urls)) { //check url has permission or not
                
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>   "Oops! you don't have sufficient permission"
                ], 403);
            }
        }else{
            $state_list = State::select('state_id_pk', 'state_name')->where('active_status', '1')->orderBy('state_id_pk', 'DESC')->get();
            if (sizeof($state_list) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'State found',
                    'count'     =>   sizeof($state_list),
                    'states'    =>  StateResource::collection($state_list)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No state available'
                );
                return response(json_encode($reponse), 404);
            }
        }
        
    }

    //District List
   public function allDistricts(Request $request, $state_code = null,$type=null)
    {
        if($type=null){
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
                        if (in_array('district-list', $url_data)) { //check url has permission or not
                            if ($state_code) {
    
                                $district_list = District::with('state:state_id_pk,state_name')->where('active_status', '1')->where('state_id_fk', $state_code)->orderBy('district_id_pk', 'DESC')->get();
                            } else {
                                $district_list = District::with('state:state_id_pk,state_name')->where('active_status', '1')->orderBy('district_id_pk', 'DESC')->get();
                            }
                            if (sizeof($district_list) > 0) {
                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'District found',
                                    'count'     =>   sizeof($district_list),
                                    'districts'  =>  DistrictResource::collection($district_list)
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No district available'
                                );
                                return response(json_encode($reponse), 200);
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

        }else{
            if ($state_code) {
                $district_list = District::with('state:state_id_pk,state_name')->where('active_status', '1')->where('state_id_fk', $state_code)->orderBy('district_id_pk', 'DESC')->get();
            } else {
                $district_list = District::with('state:state_id_pk,state_name')->where('active_status', '1')->orderBy('district_id_pk', 'DESC')->get();
            }
            if (sizeof($district_list) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'District found',
                    'count'     =>   sizeof($district_list),
                    'districts'  =>  DistrictResource::collection($district_list)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No district available'
                );
                return response(json_encode($reponse), 404);
            }

        }
        
    }

    //Institute List
     public function allInstList(Request $request, $inst_id = null, $type = null)
    {
        if($type = null){
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
    
                        if (in_array('get-all-institute-list', $url_data)) { //check url has permission or not
                            $inst_res = null;
                            $inst_list = Institute::where('inst_sl_pk', '>', 0);
                        
                            if ($inst_id) {
                                
                                $inst_list->where('inst_sl_pk', $inst_id);
                            }
                      
                            $inst_res = $inst_list->orderBy('institute_name', 'ASC')->get();
                          
    
    
                            if (sizeof($inst_res) > 0) {
                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'Institute found',
                                    'count'     =>   sizeof($inst_res),
                                    'instituteList'   =>  InstituteResource::collection($inst_res)
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No data found'
                                );
                                return response(json_encode($reponse), 200);
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
        }else{
            $inst_res = null;
                        $inst_list = Institute::where('inst_sl_pk', '>', 0);
                        // if ($inst_id) {
                        //     $inst_list->where('inst_sl_pk', $inst_id);
                        // }
                        $inst_res = $inst_list->orderBy('institute_name', 'ASC')->get();


                        if (sizeof($inst_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Institute found',
                                'count'     =>   sizeof($inst_res),
                                'instituteList'   =>  InstituteResource::collection($inst_res)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No data found'
                            );
                            return response(json_encode($reponse), 200);
                        }
            
        }
       
    }

    //Course List
     public function allCourseList(Request $request, $inst_id = null, $type = null)
    {
        if($type = null){
          
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
    
                        if (in_array('all-course', $url_data)) { //check url has permission or not
                            $course_res = null;
                            $session = SessionActive::where('is_adm_active', 1)->pluck('session_name')->toArray();
                            
                            $course_list = Course::where('course_id_pk', '>', 0)->where('is_active',1)->with('institute');

                            if ($inst_id) {
                                $course_list->where('inst_id', $inst_id)->where('is_active',1);
                            }
                            $course_res = $course_list->orderBy('course_name', 'ASC')->get();
                           
    
                            if (sizeof($course_res) > 0) {
                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'Course found',
                                    'count'     =>   sizeof($course_res),
                                    'courseList'   =>  CourseResource::collection($course_res)
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No data found'
                                );
                                return response(json_encode($reponse), 200);
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

        }else{
            $course_res = null;
            $session =SessionActive::where('is_adm_active', 1)->pluck('session_name')->toArray();
                            
            $course_list = Course::where('course_id_pk', '>', 0)->where('is_active',1)->with('institute');
            if ($inst_id) {
                $course_list->where('inst_id', $inst_id)->where('is_active',1);
            }
            $course_res = $course_list->orderBy('course_name', 'ASC')->get();
            if (sizeof($course_res) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Course found',
                    'count'     =>   sizeof($course_res),
                    'courseList'   =>  CourseResource::collection($course_res)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No data found'
                );
                return response(json_encode($reponse), 200);
            }
                      

        }
       
    }
    public function allSubdivisions(Request $request, $dist_id = null,$type=null)
    {
        if($type=null){
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
                        if (in_array('district-list', $url_data)) { //check url has permission or not
                            if ($dist_id) {
                                $subdivision_list = Subdivision::with('district:district_id_pk,district_name')->where('active_status', '1')->where('district_id', $dist_id)->orderBy('id', 'DESC')->get();
                            } else {
                                $subdivision_list = Subdivision::with('district:district_id_pk,district_name')->where('active_status', '1')->orderBy('id', 'DESC')->get();
                            }
                            if (sizeof($subdivision_list) > 0) {
                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'subdivision found',
                                    'count'     =>   sizeof($subdivision_list),
                                    'subdivisions'  =>  SubdivisionResource::collection($subdivision_list)
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'No subdivision available'
                                );
                                return response(json_encode($reponse), 200);
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

        }else{
            if ($dist_id) {
                $subdivision_list = Subdivision::with('district:district_id_pk,district_name')->where('active_status', '1')->where('district_id', $dist_id)->orderBy('id', 'DESC')->get();
            } else {
                $subdivision_list = Subdivision::with('district:district_id_pk,district_name')->where('active_status', '1')->orderBy('id', 'DESC')->get();
            }
            if (sizeof($subdivision_list) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'subdivision found',
                    'count'     =>   sizeof($subdivision_list),
                    'subdivisions'  =>  SubdivisionResource::collection($subdivision_list)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No subdivision available'
                );
                return response(json_encode($reponse), 200);
            }

        }
        

    }
    //Theory Paper List
    public function allTheoryPaperList(Request $request)
    {
       $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('all-theory-paper', $allowed_urls)) { //check url has permission or not
            $paper_res = null;
            $paper_list = Paper::where('paper_id_pk', '>', 0)->with(['institute', 'course']);
            
            $paper_res = $paper_list->orderBy('paper_id_pk', 'ASC')->get();

            if (sizeof($paper_res) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Papers  found',
                    'count'     =>   sizeof($paper_res),
                    'List'   =>  TheoryPaperResource::collection($paper_res)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No data found'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                
    }
    public function semesterList(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);

        if (in_array('semester-list', $allowed_urls)) { //check url has permission or not
            $semesters = [
                [
                    'name' => 'Semester I',
                    'value' => 'Semester I',
                ],
                [
                    'name' => 'Semester II',
                    'value' => 'Semester II',
                ],
                [
                    'name' => 'Semester III',
                    'value' => 'Semester III',
                ],
                [
                    'name' => 'Semester IV',
                    'value' => 'Semester IV',
                ],
                [
                    'name' => 'Semester V',
                    'value' => 'Semester V',
                ]
            ];

            if ($semesters > 0) {
                $reponse = array(
                    'error'   =>  false,
                    'message' =>  'Semester List Found',
                    'semester' =>    $semesters
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'         =>  true,
                    'message'       =>  'No Semester found'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
               
    }


    //Institute wise Course list 
    public function instwiseCourse(Request $request, $user_type = null)
    {
        if($user_type){
            $course_list = Course::where('is_active', 1)
                ->where(['inst_id' => $request->inst_id, 'course_affiliation_year' => $request->sessionYear])
                ->get();
                        //return $course_res;

                if (sizeof($course_list) > 0) {
                    $reponse = array(
                        'error'         =>  false,
                        'message'       =>  'Course List found',
                        'count'         =>   sizeof($course_list),
                        'lists'   =>  CourseResource::collection($course_list)
                    );
                    return response(json_encode($reponse), 200);
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'No Data available'
                    );
                    return response(json_encode($reponse), 200);
                }
            
        } else{
                 $allowed_urls = $request->get('allowed_urls', []);
                    if (in_array('course-list', $allowed_urls)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            // 'sessionYear' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $course_list = Course::where('is_active', 1)
                                ->where(['inst_id' => $request->inst_id])
                                ->get();
                        } else if ($user_data->u_role_id == '2') { 
                            $course_list = Course::where('is_active', 1)
                                ->where('inst_id', $request->inst_id)
                                ->get();
                            
                        } else if ($user_data->u_role_id == '3') {
                            // Examiner 
                            if ($user_data->is_direct == '0') {

                                //Not a special examiner

                                $internalCourseIds = DB::table('wbscte_other_diploma_examiner_institute_tag_master')->select('examiner_course_id')->where('examiner_user_id', $user_data->u_id)->where('examiner_inst_id', $request->inst_id)->where('is_active', 1)->where('map_paper_entry_type', 1)->pluck('examiner_course_id');

                                $externalCourseIds = DB::table('wbscte_external_examinner_mapping_master_tbl')->select('map_course_id')->where('map_examiner_id', $user_data->u_id)->where('is_active', 1)->where('map_assign_inst_id', $request->inst_id)->pluck('map_course_id'); //->groupBy('map_paper_id')
                            
                                $allCourseIds = $internalCourseIds->merge($externalCourseIds);

                                $course_list = Course::where('is_active', 1)
                                    ->whereIn('course_id_pk', $allCourseIds)
                                    ->get();
                            } else { //Special Examiner

                                $externalCourseIds = DB::table('wbscte_external_examinner_mapping_master_tbl')->select('map_course_id')->where('map_examiner_id', $user_data->u_id)->where('is_active', 1)->where('map_assign_inst_id', $request->inst_id)->pluck('map_course_id');

                                $course_list = Course::where('is_active', 1)
                                    ->whereIn('course_id_pk', $externalCourseIds)
                                    ->get();
                            }
                        }
                        if (sizeof($course_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Course List found',
                                'count'         =>   sizeof($course_list),
                                'lists'   =>  CourseResource::collection($course_list)
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
                        ], 403);
                    }
        }
    }

    //Institute,course,active session,semester,paper type wise paper list
    public function instCourseSessionSemPaperTypewisePaperlist(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('paper-list', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
                'course_id' => ['required'],
                'paper_type' => ['required'],
                'paper_entry_type' => ['nullable'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            
            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                $paper_list = TheorySubject::where('is_active', 1)->where(['inst_id' => $request->inst_id, 'course_id' => $request->course_id,  'paper_category' => $request->paper_type, 'paper_semester' => 'SEMESTER_I'])->orderBy('paper_id_pk', 'ASC')->get();
            } else if ($user_data->u_role_id == '2') { 
                //Institute Admin
                $paper_list = TheorySubject::where('is_active', 1)->where(['inst_id' => $request->inst_id, 'course_id' => $request->course_id,  'paper_category' => $request->paper_type, 'paper_semester' => 'SEMESTER_I'])->orderBy('paper_id_pk', 'ASC')->get();
            } else if ($user_data->u_role_id == '3') { // Examiner 
                if ($user_data->is_direct == '0') { //Not a special examiner
                    if ($request->paper_entry_type == '1') { //Internal
                        $internalPaperIds = DB::table('wbscte_other_diploma_examiner_institute_tag_master')->select('examiner_paper_id')->where('examiner_user_id', $user_data->u_id)->where('examiner_course_id', $request->course_id)->where('is_active', 1)->where('map_paper_type', $request->paper_type)->where('examiner_part_sem', 'SEMESTER_I')->pluck('examiner_paper_id');
                        $allPaperIds = $internalPaperIds;
                    } else { //Theory External
                        //return $internalPaperIds;
                        $externalPaperIds = DB::table('wbscte_external_examinner_mapping_master_tbl')->select('map_paper_id')->where('map_examiner_id', $user_data->u_id)->where('map_course_id', $request->course_id)->where('is_active', 1)->where('map_assign_inst_id', $request->inst_id)->where('map_sem', 'SEMESTER_I')->pluck('map_paper_id'); //->groupBy('map_paper_id')
                        $allPaperIds = $externalPaperIds;
                    }

                    //dd($internalPaperIds, $externalPaperIds);


                    $paper_list = TheorySubject::where('is_active', 1)->whereIn('paper_id_pk', $allPaperIds)->orderBy('paper_id_pk', 'ASC')->get();
                    //return $paper_list;
                } else { //Special Examiner
                    $externalPaperIds = DB::table('wbscte_external_examinner_mapping_master_tbl')->select('map_paper_id')->where('map_examiner_id', $user_data->u_id)->where('map_course_id', $request->course_id)->where('is_active', 1)->where('map_assign_inst_id', $request->inst_id)->where('map_sem', 'SEMESTER_I')->pluck('map_paper_id');
                    $paper_list = TheorySubject::where('is_active', 1)->whereIn('paper_id_pk', $externalPaperIds)->orderBy('paper_id_pk', 'ASC')->get();
                }
            }
            // dd($paper_list);

            if (sizeof($paper_list) > 0) {
                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Paper List found',
                    'count'         =>   sizeof($paper_list),
                    'lists'   =>  TheoryPaperResource::collection($paper_list)
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
            ], 403);
        }
                
    }

    //Active session 
    public function activeSession(Request $request, $type = null)
    {
        $allowed_urls = $request->get('allowed_urls', []);

        if (in_array('active-session', $allowed_urls)) { //check url has permission or not
            if (is_null($type)) {
                throw ValidationException::withMessages(['type' => 'type is required as path param']);
            }

            // $current_session = DB::table('wbscte_other_diploma_session_tbl')->select('session_name')->where('session_active', 1)->first();
            if ($type == 'regular') {
                $current_session = DB::table('wbscte_other_diploma_session_tbl')->select('session_name')->where('session_active', 1)->orderBy('session_id_pk')->get();
            } else {
                $current_session = DB::table('wbscte_other_diploma_session_tbl')->select('session_name')->orderBy('session_id_pk','ASC')->get();
            }

            $session = $current_session->map(function ($value) {
                return [
                    'name' => $value->session_name,
                    'value' => $value->session_name,
                ];
            });
            $currentYear = date('Y');

            $examYears = range($currentYear, $currentYear - 3);
            // return $session;

            if (sizeof($current_session) > 0) {
                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Active Session found',
                    'count'         =>   sizeof($current_session),
                    'activeSession'   =>  $session,
                    'examYears' =>  $examYears
            
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No Session available'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                
    }

    //User wise Institute List
    public function userWiseInstitute(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        $user_data = $request->get('user_data');  

        if (in_array('institute-list', $allowed_urls)) {
            $institute_list = Institute::where('is_active', 1);

            if ($user_data->u_role_id == '1') {
                // Super Admin or Council Admin
                $institute_list = $institute_list->orderBy('institute_name', 'ASC')->get();
            } elseif ($user_data->u_role_id == '2') {
                // Institute Admin
                $institute_list = $institute_list->where('inst_sl_pk', $user_data->u_inst_id)->orderBy('institute_name', 'ASC')->get();
            } elseif ($user_data->u_role_id == '3') {
                // Examiner
                $institute_ids = DB::table('wbscte_other_diploma_examiner_institute_tag_master')
                    ->where('examiner_user_id', $user_data->u_id)
                    ->where('is_active', 1)
                    ->groupBy('examiner_inst_id')
                    ->pluck('examiner_inst_id');

                $external_inst_ids = DB::table('wbscte_external_examinner_mapping_master_tbl')
                    ->where('map_examiner_id', $user_data->u_id)
                    ->where('is_active', 1)
                    ->groupBy('map_assign_inst_id')
                    ->pluck('map_assign_inst_id');

                $mergeArr = $institute_ids->merge($external_inst_ids);
                $institute_list = $institute_list->whereIn('inst_sl_pk', $mergeArr)->get();
            }

            if (sizeof($institute_list) > 0) {
                $reponse = array(
                    'error' => false,
                    'message' => 'Institute List found',
                    'count' => sizeof($institute_list),
                    'list' => InstituteResource::collection($institute_list)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error' => true,
                    'message' => 'No Data available'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => "Oops! you don't have sufficient permission"
            ], 403);
        }
               
    }

    public function examinerList(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('examiner-list', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'is_direct' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            } else {
                $is_direct = $request->is_direct;

                $examiner_list = User::where('u_role_id', 3)
                    ->where('is_active', 1)
                    ->where('is_direct', $is_direct)
                    ->get();

                if (sizeof($examiner_list) > 0) {
                    $reponse = array(
                        'error'         =>  false,
                        'message'       =>  'Examiner List found',
                        'count'         =>   sizeof($examiner_list),
                        'list'   =>  UserResource::collection($examiner_list)
                    );
                    return response(json_encode($reponse), 200);
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'No Data available'
                    );
                    return response(json_encode($reponse), 200);
                }
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
    
    }
    public function editCourse(Request $request, $id)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('edit-course', $allowed_urls)) { //check url has permission or not
            $course_list = Course::where('course_id_pk', $id)->where('is_active', '1')->orderBy('course_id_pk', 'DESC')->get();
            if ($course_list) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Course found',
                    'count'     =>  sizeof($course_list),
                    'courses'  =>  json_encode($course_list)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No course available with this id!'
                );
                return response(json_encode($reponse), 404);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  "Oops! you don't have sufficient permission"
            ], 403);
        }
                
    }
    public function updateCourse(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('update-course', $allowed_urls)) { //check url has permission or not
            DB::beginTransaction();
            try {
                $course = Course::where('course_id_pk', $request->course_id)->first();
                
                if ($course) {
                    $course_id = $course->course_id_pk;
                    $course->course_type  =  $request->course_type;
                    $course->course_duration = $request->course_duration;
                    $course->course_code = $request->course_code;
                    $course->course_name = $request->course_name;
                    $course->inst_id = $request->inst_id;
                    $course->is_active = $request->is_active;
                    $course->save();
                    DB::commit();
                    return response()->json([
                        'error'     =>  false,
                        'message'   =>  'Course updated successfully'
                    ], 200);
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'Course not found'
                    );
                    return response(json_encode($reponse), 404);
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
            ], 403);
        }
                  
    }
    public function updateInstitute(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('update-institute', $allowed_urls)) { //check url has permission or not
            DB::beginTransaction();
            try {
                $institute = Institute::where('inst_sl_pk', $request->institute_id)->first();
                // dd($institute);

                if ($institute) {
                    $institute_id = $institute->inst_sl_pk;
                    $institute->institute_code  =  $request->institute_code;
                    $institute->institute_name = $request->institute_name;
                    $institute->institute_address = $request->institute_address;
                    $institute->is_active = $request->institute_active;
                    // dd($institute);
                    $institute->save();
                    DB::commit();
                    return response()->json([
                        'error'     =>  false,
                        'message'   =>  'Institute updated successfully'
                    ], 200);
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'Institute not found'
                    );
                    return response(json_encode($reponse), 404);
                }
            } catch (Exception $e) {
                DB::rollback();
                return response()->json([
                    'error'     =>  true,
                    'message'   => 'An error has occurred' //$e->getMessage()
                ], 400);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  "Oops! you don't have sufficient permission"
            ], 403);
        }
                   
    }
    public function updatePaper(Request $request)
    {
         // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    'paper_id' => ['required'],
                    'course_id' => ['required'],
                    'inst_id' => ['required'],
                    'paper_code'=> ['required'],
                    'paper_name'=> ['required'],
                    'paper_type'=> ['required'],
                    'paper_semester' => ['required'],
                    'paper_category' => ['required'],
                    'paper_credit' => ['required'],
                    'paper_fullmarks' => ['required'],
                    'paper_internalmarks' => ['required'],
                    'paper_externalmarks' => ['required'],
                    'paper_theory_viva_marks' => ['nullable'],
                    'paper_theory_classtest_marks' => ['nullable'],
                    'paper_theory_attend_marks' => ['nullable'],
                    'paper_sess_attendance_marks' => ['nullable'],
                    'paper_sess_viva_marks' => ['nullable'],
                    'paper_sess_classtest_marks' => ['nullable'],
                    'paper_sess_assign_viva_marks' => ['nullable'],
                    'paper_sess_before_viva_marks' => ['nullable'],
                    'paper_affiliation_year' => ['required'],
                    'paper_pass_marks' =>['required'],
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages()
                    ], 400);
                } else {
                    $allowed_urls = $request->get('allowed_urls', []);
                    if (in_array('update-institute', $allowed_urls)) { //check url has permission or not
                        DB::beginTransaction();
                        try {
                            $msg = false;
                            $inst_id = $request->inst_id;
                            $course_id = $request->course_id;
                            $paper_code = $request->paper_code;
                            $paper_name = $request->paper_name;
                            $paper_semester = $request->paper_semester;
                            $paper_type= $request->paper_type;
                            $paper_category = $request->paper_category;
                            $paper_credit = $request->paper_credit;
                            $paper_fullmarks = (int)$request->paper_fullmarks;
                            $paper_internalmarks = (int)$request->paper_internalmarks;
                            $paper_externalmarks =(int)$request->paper_externalmarks;
                            $paper_theory_viva_marks = (int)$request->paper_theory_viva_marks;
                            $paper_theory_classtest_marks = (int)$request->paper_theory_classtest_marks;
                            $paper_theory_attend_marks =  (int)$request->paper_theory_attend_marks;
                            $paper_sess_attendance_marks = (int)$request->paper_sess_attendance_marks;
                            $paper_sess_viva_marks = (int)$request->paper_sess_viva_marks;
                            $paper_sess_classtest_marks = (int)$request->paper_sess_classtest_marks;
                            $paper_sess_assign_viva_marks = (int)$request->paper_sess_assign_viva_marks;
                            $paper_sess_before_viva_marks = (int)$request->paper_sess_before_viva_marks;
                            $paper_affiliation_year = $request->paper_affiliation_year;
                            $paper_pass_marks = $request->paper_pass_marks;
                            $exam_year = $request->exam_year;
                            $check_paper = Paper::where([
                                'paper_affiliation_year'=>$paper_affiliation_year,
                                'paper_semester'=>$paper_semester,
                                'paper_category' =>$paper_category,
                                'inst_id' => $inst_id,
                                'course_id' => $course_id,
                                'paper_code' =>$paper_code

                            ])->whereNotIn('paper_id_pk',[$request->paper_id])->first();
                            if($check_paper){
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Data already exist,please try another!'
                                ], 200);
                            }else{
                                $sum_total_marks = ($paper_internalmarks + $paper_externalmarks);;
                                if(($sum_total_marks > $paper_fullmarks)){
                                    return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Input marks can not be greater than full marks!',
                                    ], 200);

                                }else if(($sum_total_marks < $paper_fullmarks)){
                                    return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Input marks can not be Less than full marks!'
                                    ], 200);

                                }
                                if($paper_category == 1){//for theory paper
                                    $sum_internal_th =  ($paper_theory_viva_marks + $paper_theory_classtest_marks + $paper_theory_attend_marks);
                                    
                                    if(($sum_internal_th > $paper_internalmarks)){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be greater than theory marks!'
                                        ], 200);
                                    }else if(($sum_internal_th < $paper_internalmarks)){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be less than theory marks!'
                                        ], 200);
                                    }
                                    $msg = true;
                                }else {
                                    $sum_internal_sess =  ($paper_sess_attendance_marks + $paper_sess_classtest_marks + $paper_sess_viva_marks);
                                    $sum_external_sess = ($paper_sess_assign_viva_marks + $paper_sess_before_viva_marks);
                                    // dd($sum_internal_sess,$paper_internalmarks,$sum_external_sess,$paper_externalmarks);
                                    if($sum_internal_sess > $paper_internalmarks){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be greater than sessional marks!'
                                        ], 200);

                                    }else if($sum_internal_sess < $paper_internalmarks){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be less than sessional marks!'
                                        ], 200);

                                    }else if($sum_external_sess > $paper_externalmarks){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be greater than external marks!'
                                        ], 200);

                                    }else if($sum_external_sess < $paper_externalmarks){
                                        return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Input marks can not be less than external marks!'
                                        ], 200);
                                    }
                                    $msg = true;
                                }
                                if($msg){
                                    $paper = TheorySubject::where('paper_id_pk', $request->paper_id)->first();
                                    if($paper){
                                            $paper_id = $paper->paper_id_pk;
                                            $paper->paper_code  =  $paper_code;
                                            $paper->inst_id  =  $inst_id;
                                            $paper->course_id  =  $course_id;
                                            $paper->paper_name = $paper_name;
                                            $paper->paper_type = $paper_type;
                                            $paper->paper_credit = $paper_credit;
                                            $paper->inst_id  =  $inst_id;
                                            $paper->course_id = $course_id;
                                            $paper->paper_semester = $paper_semester;
                                            $paper->paper_category = $paper_category;
                                            $paper->paper_full_marks = $paper_fullmarks;
                                            $paper->paper_internal_marks = $paper_internalmarks;
                                            $paper->paper_external_marks = $paper_externalmarks;
                                            $paper->paper_pass_marks = $paper_pass_marks;
                                            $paper->paper_internal_theory_class_test_marks =  !empty($paper_theory_classtest_marks) ? $paper_theory_classtest_marks : NULL;
                                            $paper->paper_internal_theory_viva_marks = !empty($paper_theory_viva_marks) ?$paper_theory_viva_marks : NULL;
                                            $paper->paper_internal_attendance_marks = !empty($paper_theory_attend_marks) ? $paper_theory_attend_marks : NULL;
                                            $paper->paper_sessional_internal_attendance_marks = !empty($paper_sess_attendance_marks) ? $paper_sess_attendance_marks : NULL;
                                            $paper->paper_sessional_internal_viva_marks = !empty($paper_sess_viva_marks)? $paper_sess_viva_marks : NULL;
                                            $paper->paper_sessional_internal_class_test_marks = !empty($paper_sess_classtest_marks) ? $paper_sess_classtest_marks : NULL;
                                            $paper->paper_sess_assign_viva_marks = !empty($paper_sess_viva_marks) ? $paper_sess_viva_marks : NULL;
                                            $paper->paper_sess_before_viva_marks =!empty($paper_sess_before_viva_marks) ? $paper_sess_before_viva_marks : NULL;
                                            $paper->paper_affiliation_year = $paper_affiliation_year;
                                            $paper->exam_year = $exam_year;
                                            $paper->is_active = $request->is_active;
                                            $paper->save();


                                    }
                                    $last_insert_id = $request->paper_id;
                                    auditTrail($token_user_id, "paper with ID {$last_insert_id} Updated.");
                                    DB::commit();
                                    $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Paper Updated Successfully',
                                    );
                                    return response(json_encode($reponse), 200);

                                }
                            }
                        } catch (Exception $e) {
                            DB::rollback();
                            //dd($e->getMessage());
                            return response()->json([
                                'error'     =>  true,
                                'message'   => 'An error has occurred' //$e->getMessage()
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>  "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                    
                }
            
    }
    //Paper edit
    public function editPaper(Request $request, $id)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('all-theory-paper', $allowed_urls)) { //check url has permission or not
            $paper_list = Paper::where('paper_id_pk', $id)->with(['institute', 'course']);
            
            $paper_res = $paper_list->orderBy('paper_id_pk', 'DESC')->first();

            if ($paper_res) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Paper  found',
                    'count'     =>   1,
                    'details'   =>  new TheoryPaperResource($paper_res)
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No data found'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  "Oops! you don't have sufficient permission"
            ], 403);
        }
               
    }
    public function checkPaper(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('individual-attendance', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
                'semester' => ['required']
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }

            $inst_id = $request->inst_id;
            $semester = $request->semester;
            $current_session = DB::table('wbscte_other_diploma_session_tbl')->select('session_name')->where('session_active', 1)->first();
            $paper_affiliation_year = getFinancialYear($current_session->session_name, 'regular');


            $paper = TheorySubject::where(['inst_id' => $inst_id, 'paper_semester' => $semester, 'is_active' => 1, 'paper_affiliation_year' => $paper_affiliation_year[0], 'paper_category' => 1])->count();

            if ($paper > 0) {
                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Data found'
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'         =>  true,
                    'message'       =>  'No Paper found for this institute, try another'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                
    }
    public function paperTypeList(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('paper-type-list', $allowed_urls)) {
            $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
                'course_id' => ['required']
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            } else {
                $inst_id = $request->inst_id;
                $course_id = $request->course_id;

                $type1 = 'Theory';
                $type2 = 'Sessional';


                if ($user_data->u_role_id == '1') {
                    //Super Admin or Council Admin
                    $paper_types = [
                        [
                            'name' => $type1,
                            'value' => 1
                        ],
                        [
                            'name' => $type2,
                            'value' => 2
                        ],
                    ];
                } else if ($user_data->u_role_id == '2') {
                    //Institute Admin
                    $paper_types = [
                        [
                            'name' => $type1,
                            'value' => 1
                        ],
                        [
                            'name' => $type2,
                            'value' => 2
                        ],
                    ];
                } else if ($user_data->u_role_id == '3') {
                    // Examiner

                    $paper_types = [];

                    $entry_type = $request->entry_type;

                    $int_type = OtherDiplomaExaminnerInstitute::where([
                        'examiner_user_id' => $user_id,
                        'examiner_inst_id' => $inst_id,
                        'examiner_course_id' => $course_id,
                        'is_active' => 1
                    ])->get();
                    if($entry_type == 1){
                        foreach($int_type as $val){
                            if ($val) {
                                if ($val->map_paper_type == 1) {
                                    if (!searchAssociative($paper_types, 'name', $type1)) {
                                        array_push($paper_types, [
                                            'name' => $type1,
                                            'value' => 1
                                        ]);
                                    }
                                } else if ($val->map_paper_type == 2) {
                                    if (!searchAssociative($paper_types, 'name', $type2)) {
                                        array_push($paper_types, [
                                            'name' => $type2,
                                            'value' => 2
                                        ]);
                                    }
                                }
                            }
                        }

                    }else if($entry_type == 2){
                        $ext_type = ExternelExaminerMap::where([
                            'map_examiner_id' => $user_id,
                            'map_assign_inst_id' => $inst_id,
                            'map_course_id' => $course_id,
                            'is_active' => 1
                        ])->get();
                        foreach($ext_type as $val){
                            if ($val) {
                                if ($val->map_paper_type == 1) {
                                    if (!searchAssociative($paper_types, 'name', $type1)) {
                                        array_push($paper_types, [
                                            'name' => $type1,
                                            'value' => 1
                                        ]);
                                    }
                                } else if ($val->map_paper_type == 2) {
                                    if (!searchAssociative($paper_types, 'name', $type2)) {
                                        array_push($paper_types, [
                                            'name' => $type2,
                                            'value' => 2
                                        ]);
                                    }
                                }
                            }

                        }

                    }
                }
            }

            if ($paper_types > 0) {
                $reponse = array(
                    'error'   =>  false,
                    'message' =>  'Data found',
                    'type' =>    $paper_types
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'         =>  true,
                    'message'       =>  'No Semester found'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
             
    }
    public function entryTypeList(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('entry-type-list', $allowed_urls)) {
            $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
                'course_id' => ['required']
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            } else {
                $inst_id = $request->inst_id;
                $course_id = $request->course_id;

                $type1 = 'Internal';
                $type2 = 'External';

                if ($user_data->u_role_id == '1') {
                    //Super Admin or Council Admin
                    $entry_types = [
                        [
                            'name' => $type1,
                            'value' => 1
                        ],
                        [
                            'name' => $type2,
                            'value' => 2
                        ],
                    ];
                } else if ($user_data->u_role_id == '2') {
                    //Institute Admin
                    $entry_types = [
                        [
                            'name' => $type1,
                            'value' => 1
                        ],
                        [
                            'name' => $type2,
                            'value' => 2
                        ],
                    ];
                } else if ($user_data->u_role_id == '3') {
                    $entry_types = [];

                    $int_type = OtherDiplomaExaminnerInstitute::where([
                        'examiner_user_id' => $user_id,
                        'examiner_inst_id' => $inst_id,
                        'examiner_course_id' => $course_id,
                        'is_active' => 1
                    ])->first('map_paper_entry_type');
                    $ext_type = ExternelExaminerMap::where([
                        'map_examiner_id' => $user_id,
                        'map_assign_inst_id' => $inst_id,
                        'map_course_id' => $course_id,
                        'is_active' => 1
                    ])->first('map_paper_entry_type');

                    if($int_type && $ext_type){
                        if ($int_type->map_paper_entry_type == 1 && $ext_type->map_paper_entry_type == 2) {
                            if (!searchAssociative($entry_types, 'name', $type1)) {
                                array_push($entry_types, [
                                    'name' => $type1,
                                    'value' => 1
                                ]);
                            }
                            if (!searchAssociative($entry_types, 'name', $type2)) {
                                array_push($entry_types, [
                                    'name' => $type2,
                                    'value' => 2
                                ]);
                            }
                        } 

                    }else {
                        if ($int_type) {
                            if ($int_type->map_paper_entry_type == 1) {
                                if (!searchAssociative($entry_types, 'name', $type1)) {
                                    array_push($entry_types, [
                                        'name' => $type1,
                                        'value' => 1
                                    ]);
                                }
                            }
                        }
                    
                        if ($ext_type) {
                            if ($ext_type->map_paper_entry_type == 2) {
                                if (!searchAssociative($entry_types, 'name', $type2)) {
                                    array_push($entry_types, [
                                        'name' => $type2,
                                        'value' => 2
                                    ]);
                                }
                            }
                        }
                    }
                            
                }

                
            }


            if ($entry_types > 0) {
                $reponse = array(
                    'error'   =>  false,
                    'message' =>  'Data found',
                    'type' =>    $entry_types
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'         =>  true,
                    'message'       =>  'No Semester found'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
               
    }
    public function instituteWiseExaminer(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('institute-wise-examiner', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }

            $Examiner_list = OtherDiplomaExaminnerInstitute::where('examiner_inst_id', $request->inst_id)->where('is_active', 1)->distinct('examiner_user_id')->select('examiner_id', 'examiner_name', 'examiner_user_id')
                ->with('user')->get();
                


            if (sizeof($Examiner_list) > 0) {
                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Examiner List found',
                    'count'         =>   sizeof($Examiner_list),
                    'lists'   =>  ExaminerInternalResource::collection($Examiner_list)
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
            ], 403);
        }
    }
   
    public function createConfigMarks(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('create-cnfg-marks', $allowed_urls)) {
            $validated = Validator::make($request->all(), [
                'semester' => [
                    'required',
                ],
                'schedule_type' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $q = \DB::table('wbscte_other_diploma_config_marks')
                        ->select(\DB::raw('COUNT(*) as item_count'))
                        ->where('semester', request()->semester)
                        ->where('config_for', $value)
                        ->first();

                        if ($q->item_count >= 1) {
                        $fail("The " . str_replace('_', ' ', $attribute) . " is already taken for {$value}");
                        }
                    }
                ],
                'start_date' => [
                    'required',
                ],
                'end_date' => [
                    'required'
                ],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }  try {
                DB::beginTransaction();
                $stDate = "{$request->start_date} 10:00:01";
                $endDate = "{$request->end_date} 23:59:59";
                $semester= $request->semester;
                $schedule_type = $request->schedule_type;

                $token = Token::where('t_token', '=', $request->header('token'))->first();
                
                $user_id = $token->t_user_id;

                CnfgMarks::create([
                    'semester'  => $semester,
                    'config_for' => $schedule_type,
                    'start_at' => $stDate,
                    'end_at' => $endDate,
                    'created_by' => $user_id,
                ]);
                
                auditTrail($user_id, "Schedule for {$schedule_type} Created");
                DB::commit();

                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Schedule  Created Successfully',
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
            ], 403);
        }
               
    }

    public function updateConfigMarks(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('update-cnfg-marks', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'semester' => ['required'],
                'schedule_type' => ['required'],
                'start_date' => ['required'],
                'end_date' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            try {
                DB::beginTransaction();

                $stDate = "{$request->start_date} 10:00:01";
                $endDate = "{$request->end_date} 23:59:59";
                $semester= $request->semester;
                $schedule_type = $request->schedule_type;
                $id = $request->id;

                $schedule = CnfgMarks::findOrFail($id);

                if ($schedule) {
                    $schedule->update([
                        'semester' => $semester,
                        'config_for' => $schedule_type,
                        'start_at' => $stDate,
                        'end_at' => $endDate,
                        'updated_by' => $user_id
                    ]);
                
                }

                DB::commit();

                auditTrail($user_id, "Schedule for {$schedule_type} Updated");

                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Schedule Updated Successfully',
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
            ], 403);
        }
    }
    public function deleteConfigMarks(Request $request,$id)
    {
         $allowed_urls = $request->get('allowed_urls', []);
    
        if (in_array('delete-cnfg-marks', $allowed_urls)) { //check url has permission or not
            try {
                $schedule = CnfgMarks::findOrFail($id);

                if ($schedule) {
                    $now = date('Y-m-d H:i:s');

                    if ($now >= $schedule->start_at && $now <= $schedule->end_at) {
                        $reponse = array(
                            'error'         =>  true,
                            'message'       =>  'Oops! Schedule Already Started',
                        );
                        return response(json_encode($reponse), 200);
                    } else {
                        DB::beginTransaction();

                        $schedule->delete();

                        auditTrail($user_id, "Marks schedule config deleted");

                        DB::commit();

                        $reponse = array(
                            'error'         =>  false,
                            'message'       =>  'Schedule Deleted Successfully',
                        );
                        return response(json_encode($reponse), 200);
                    }
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
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                  

    }
    public function getConfigMarks(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('get-cnfg-marks', $allowed_urls)) {
            $validated = Validator::make($request->all(), [
                'semester' => ['nullable'],
                'schedule_type' => ['nullable'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  $validated->errors()
                ], 422);
            } else {
                $res = CnfgMarks::where('id', '>', 0)->get();

                if (sizeof($res) > 0) {
                    $reponse = array(
                        'error'     =>  false,
                        'message'   =>  'Data found',
                        'data'   =>  ScheduleResource::collection($res)
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
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
               
    }
    public function checkScheduleConfig(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);

        if (in_array('check-cnfg-marks-schedule', $allowed_urls)) { //check url has permission or not
            $res = null;
            $validated = Validator::make($request->all(), [
                'semester' => ['required'],
                'type' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            $result = CnfgMarks::where([
                'semester' => $request->semester,
                'config_for' => $request->type
            ])
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->exists();
        

            if ($result) {
                return response()->json([
                    'error' => false,
                    'data_exists' => true,
                    'message' => 'Schedule found'
                ], 200);
            } 
            $messages = [
                'MARKS_ENTRY' => 'You are unable to marks entry, your marks entry time is expired, please contact admin for details',
                'ATTENDANCE' => 'You are unable to attendance, your attendance time is expired, please contact admin for details',
                'ENROLLMENT' => 'You are unable to Enrollment, your enrollment time is expired, please contact admin for details'
            ];
            return response()->json([
                'error' => true,
                'data_exists' => false,
                'message' => $messages[$request->type] ?? 'Invalid type'
            ], 200);
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }    
             
    }
    public function examinerNotExistInstitute(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('examiner-notexist-institute', $allowed_urls)) {
                //check url has permission or not
                $validated = Validator::make($request->all(), [
                'inst_id' => ['required'],
                'semester' => ['required'],
                'course_id' => ['required'],
                'paper_id' => ['required'],
                'paper_type' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
                $inst_id = $request->inst_id;                      
                $semester = $request->semester;                      
                $course_id = $request->course_id;                      
                $paper_id = $request->paper_id;         
                $paper_type = $request->paper_type;         

                // $internal_ids = OtherDiplomaExaminnerInstitute::where(['examiner_inst_id'=> $inst_id,'examiner_part_sem' => $semester,'examiner_course_id' => $course_id,'examiner_paper_id'=> $paper_id,'map_paper_type'=>$paper_type])->distinct()->pluck('examiner_user_id');
                $internal_ids = OtherDiplomaExaminnerInstitute::where(['examiner_inst_id'=> $inst_id,'examiner_part_sem' => $semester,'examiner_course_id' => $course_id])
                ->distinct()->pluck('examiner_user_id');

                $external_examiner_list = User::whereIn('u_id',$internal_ids)->where('is_active',1)->where('u_role_id',3)->where('is_direct',0)->get()->map(function ($value){
                    return [
                        'examiner_id' => $value->u_id,
                        'examiner_name'=> $value->u_fullname,
                        'examiner_phone'=> $value->u_phone,
                    ];

                });
            
            
            if (sizeof($external_examiner_list) > 0) {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Examiner found',
                    'count'     =>  sizeof($external_examiner_list),
                    'list'     =>  $external_examiner_list
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No Examiner available'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  "Oops! you don't have sufficient permission"
            ], 403);
        }
              

    }
    public function createUser(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);

        if (in_array('create-user', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'username' => ['required'],
                'fullname' => ['required'],
                'role' => ['required'],
                'phone' => 'required|unique:wbscte_other_diploma_users_master,u_phone',
                'email' => ['required'],
                // 'is_active' => ['required'],
                'inst_id' => ['nullable'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            try {
                DB::beginTransaction();
                $now    =   date('Y-m-d H:i:s');
                $username = $request->username;
                $fullname = $request->fullname;
                $role = $request->role;
                $phone = $request->phone;
                $email = $request->email;
                // $active = $request->is_active;
                $inst_id = $request->inst_id;
                //dd($inst_id);
                // DB::connection()->enableQueryLog();
                User::create([
                    'u_username'  => $username,
                    'u_fullname' => $fullname,
                    'u_phone'  => $phone,
                    'u_email'    => $email,
                    'u_role_id' => $role,
                    'is_active' => 1,
                    'created_at' => $now,
                    'u_inst_id' =>  $inst_id,
                    'is_direct' =>  0,
                ]);
                // $queries = DB::getQueryLog();
                // dd($queries);
                $last_insert_id = DB::getPdo()->lastInsertId();
                auditTrail($user_id, "User with ID {$last_insert_id} Created.");
                DB::commit();

                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'User Created Successfully',
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
            ], 403);
        }
    

    }
    public function createPaper(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('create-paper', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'course_id' => ['required'],
                'inst_id' => ['required'],
                'paper_code'=> ['required'],
                'paper_name'=> ['required'],
                'paper_type'=> ['required'],
                'paper_semester' => ['required'],
                'paper_category' => ['required'],
                'paper_credit' => ['required'],
                'paper_fullmarks' => ['required'],
                'paper_internalmarks' => ['required'],
                'paper_externalmarks' => ['required'],
                'paper_theory_viva_marks' => ['nullable'],
                'paper_theory_classtest_marks' => ['nullable'],
                'paper_theory_attend_marks' => ['nullable'],
                'paper_sess_attendance_marks' => ['nullable'],
                'paper_sess_viva_marks' => ['nullable'],
                'paper_sess_classtest_marks' => ['nullable'],
                'paper_sess_assign_viva_marks' => ['nullable'],
                'paper_sess_before_viva_marks' => ['nullable'],
                'paper_affiliation_year' => ['required'],
                'paper_pass_marks' =>['required'],

            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            try {
                DB::beginTransaction();
                $msg = false;
                $inst_id = $request->inst_id;
                $course_id = $request->course_id;
                $paper_code = $request->paper_code;
                $paper_name = $request->paper_name;
                $paper_semester = $request->paper_semester;
                $paper_type= $request->paper_type;
                $paper_category = $request->paper_category;
                $paper_credit = $request->paper_credit;
                $paper_fullmarks = (int)$request->paper_fullmarks;
                $paper_internalmarks = (int)$request->paper_internalmarks;
                $paper_externalmarks =(int)$request->paper_externalmarks;
                $paper_theory_viva_marks = (int)$request->paper_theory_viva_marks;
                $paper_theory_classtest_marks = (int)$request->paper_theory_classtest_marks;
                $paper_theory_attend_marks =  (int)$request->paper_theory_attend_marks;
                $paper_sess_attendance_marks = (int)$request->paper_sess_attendance_marks;
                $paper_sess_viva_marks = (int)$request->paper_sess_viva_marks;
                $paper_sess_classtest_marks = (int)$request->paper_sess_classtest_marks;
                $paper_sess_assign_viva_marks = (int)$request->paper_sess_assign_viva_marks;
                $paper_sess_before_viva_marks = (int)$request->paper_sess_before_viva_marks;
                $paper_affiliation_year = $request->paper_affiliation_year;
                $paper_pass_marks = $request->paper_pass_marks;
                $exam_year = $request->exam_year;
                $check_paper = Paper::where([
                    'paper_affiliation_year'=>$paper_affiliation_year,
                    'paper_semester'=>$paper_semester,
                    'paper_category' =>$paper_category,
                    'inst_id' => $inst_id,
                    'course_id' => $course_id,
                    'paper_code' =>$paper_code

                ])->first();
                if($check_paper){
                        return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Data already exist,please try another!'
                ], 200);
                }else{
                    $sum_total_marks = ($paper_internalmarks + $paper_externalmarks);
                    if(($sum_total_marks > $paper_fullmarks)){
                        return response()->json([
                        'error'     =>  true,
                        'message'   =>  'Input marks can not be greater than full marks!',
                        ], 200);

                    }else if(($sum_total_marks < $paper_fullmarks)){
                        return response()->json([
                        'error'     =>  true,
                        'message'   =>  'Input marks can not be Less than full marks!'
                        ], 200);

                    }

                    if($paper_category == 1){//for theory paper
                        $sum_internal_th =  ($paper_theory_viva_marks + $paper_theory_classtest_marks + $paper_theory_attend_marks);
                        // dd($sum_internal_th,$paper_internalmarks);
                        if(($sum_internal_th > $paper_internalmarks)){
                            return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be greater than theory marks!'
                            ], 200);
                        }else if(($sum_internal_th < $paper_internalmarks)){
                                return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be less than theory marks!'
                            ], 200);
                        }
                        $msg = true;
                    }else {//for sessional paper
                        $sum_internal_sess =  ($paper_sess_attendance_marks + $paper_sess_classtest_marks + $paper_sess_viva_marks);
                        $sum_external_sess = ($paper_sess_assign_viva_marks + $paper_sess_before_viva_marks);
                        // dd($sum_internal_sess,$paper_internalmarks,$sum_external_sess,$paper_externalmarks);
                        if($sum_internal_sess > $paper_internalmarks){
                                return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be greater than sessional marks!'
                            ], 200);

                        }else if($sum_internal_sess < $paper_internalmarks){
                                return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be less than sessional marks!'
                            ], 200);

                        }else if($sum_external_sess > $paper_externalmarks){
                                return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be greater than external marks!'
                            ], 200);

                        }else if($sum_external_sess < $paper_externalmarks){
                                return response()->json([
                            'error'     =>  true,
                            'message'   =>  'Input marks can not be less than external marks!'
                            ], 200);
                        }
                        $msg = true;

                    }
                
                    if($msg){
                        //dd("ghh");
                        $data = [
                            'paper_name'  => $paper_name,
                            'paper_code' => $paper_code,
                            'inst_id'  => $inst_id,
                            'course_id'    => $course_id,
                            'paper_semester' => $paper_semester,
                            'paper_type' => $paper_type,
                            'paper_category' => $paper_category,
                            'paper_credit' =>  $paper_credit,
                            'paper_full_marks' =>   $paper_fullmarks,
                            'paper_internal_marks' =>  $paper_internalmarks,
                            'paper_external_marks' =>  $paper_externalmarks,
                            'paper_pass_marks'=> $paper_pass_marks,
                            'paper_internal_theory_class_test_marks' =>  !empty($paper_theory_classtest_marks) ? $paper_theory_classtest_marks : NULL ,
                            'paper_internal_theory_viva_marks' => !empty($paper_theory_viva_marks) ?$paper_theory_viva_marks : NULL,
                            'paper_internal_attendance_marks' =>  !empty($paper_theory_attend_marks) ? $paper_theory_attend_marks : NULL,
                            'paper_sessional_internal_attendance_marks' =>  !empty($paper_sess_attendance_marks) ? $paper_sess_attendance_marks : NULL,
                            'paper_sessional_internal_viva_marks' => !empty($paper_sess_viva_marks)? $paper_sess_viva_marks : NULL,
                            'paper_sessional_internal_class_test_marks'=> !empty($paper_sess_classtest_marks) ? $paper_sess_classtest_marks : NULL,
                            'paper_sess_assign_viva_marks'=> !empty($paper_sess_viva_marks) ? $paper_sess_viva_marks : NULL,
                            'paper_sess_before_viva_marks'=> !empty($paper_sess_before_viva_marks) ? $paper_sess_before_viva_marks : NULL,
                            'paper_affiliation_year' => $paper_affiliation_year,
                            'exam_year'=> $exam_year,
                            ];
                            
                            Paper::create($data);
                    



                        // $queries = DB::getQueryLog();
                        // dd($queries);
                        $last_insert_id = DB::getPdo()->lastInsertId();
                        auditTrail($user_id, "paper with ID {$last_insert_id} Created.");
                        DB::commit();

                        $reponse = array(
                        'error'         =>  false,
                        'message'       =>  'Paper Created Successfully',
                        );
                        return response(json_encode($reponse), 200);
                    }
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
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
               

    }
    public function createCourse(Request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);

        if (in_array('create-course', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                'course_code' => ['required'],
                'inst_id' => ['required'],
                'course_name' => ['required'],
                'course_duration' => ['required'],
                'course_affiliation_year' => ['required'],
                'course_type' => ['required'],
                'exam_year' =>  ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            try {
                DB::beginTransaction();
                $course_code = $request->course_code;
                $inst_id = $request->inst_id;
                $course_name = $request->course_name;
                $course_duration = $request->course_duration;
                $course_affiliation_year = $request->course_affiliation_year;
                $course_type = $request->course_type;
                $exam_year = $request->exam_year;
                $check_course = Course::where([
                    'course_affiliation_year'=>$course_affiliation_year,
                    'inst_id' => $inst_id,
                    'course_code'=> $course_code,
                ])->first();
                if($check_course){
                    return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Already given!"
                    ], 200);
                }else{
                        Course::create([
                    'course_code'  => $course_code,
                    'course_name' => $course_name,
                    'inst_id'  => $inst_id,
                    'course_duration'    => $course_duration,
                    'course_affiliation_year' => $course_affiliation_year,
                    'course_type' => $course_type,
                    'exam_year' => $exam_year,
                ]);

                }
                $last_insert_id = DB::getPdo()->lastInsertId();
                auditTrail($user_id, "Course with ID {$last_insert_id} Created.");
                DB::commit();

                $reponse = array(
                    'error'         =>  false,
                    'message'       =>  'Course Created Successfully',
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
            ], 403);
        }
               
        

    }
    public function createInstitute(request $request)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('create-institute', $allowed_urls)) { //check url has permission or not
                $validated = Validator::make($request->all(), [
                    'institute_code' => ['required'],
                    'institute_name' => ['required'],
                    'institute_address' => ['required'],
                ]);

                if ($validated->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validated->errors()
                    ]);
                }
                try {
                    DB::beginTransaction();
                    $institute_code = $request->institute_code;
                    $institute_name = $request->institute_name;
                    $institute_address = $request->institute_address;
                
                            Institute::create([
                        'institute_code'  => $institute_code,
                        'institute_name' => $institute_name,
                        'institute_address'  => $institute_address,
                        
                    ]);
                    $last_insert_id = DB::getPdo()->lastInsertId();
                    auditTrail($user_id, "Institute with ID {$last_insert_id} Created.");
                    DB::commit();

                    $reponse = array(
                        'error'         =>  false,
                        'message'       =>  'Institute Created Successfully',
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
                ], 403);
        }
               

    }

    //changes dashboard
    public function countDashboard(Request $request)
    {
         $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('count-dashboard', $allowed_urls)) { //check url has permission or not
            $validated = Validator::make($request->all(), [
                // 'inst_id' => ['required'],
                'sessionYear' => ['required'],
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()
                ]);
            }
            // dd($user_data->u_role_id);
            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                $student_list_count = Student::where('student_is_enrolled', 1)
                    ->where(['student_session_yr' => $request->sessionYear])
                    ->count();
                $course_list_count = Course::where('is_active', 1)
                    ->where(['course_affiliation_year' => $request->sessionYear])
                    ->count();
                $paper_list_count = Paper::where('is_active', 1)
                    ->where(['paper_affiliation_year' => $request->sessionYear])
                    ->count();    


            } else if ($user_data->u_role_id == '2') { //Institute Admin
                $student_list_count = Student::where('student_is_enrolled', 1)
                    ->where(['student_inst_id' => $request->inst_id, 'student_session_yr' => $request->sessionYear])
                    ->count();
                $course_list_count = Course::where('is_active', 1)
                    ->where(['inst_id' => $request->inst_id, 'course_affiliation_year' => $request->sessionYear])
                    ->count(); 
                $paper_list_count = Paper::where('is_active', 1)
                    ->where(['inst_id' => $request->inst_id, 'paper_affiliation_year' => $request->sessionYear])
                    ->count();    

            } 
            return response()->json([
                'error' => false,
                'message' => 'Data found',
                'dashboard_count' => [
                    'student_count' =>  $student_list_count,
                    'course_count' =>   $course_list_count,
                    'paper_count' =>  $paper_list_count,
                    
                ]
            ]);
            
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                  
    }
    public function eligibilityList(Request $request,$user_type = null)
    {
        $validated = Validator::make($request->all(), [
            'course_code' => ['required']
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }else{
            if($user_type=null){
                $allowed_urls = $request->get('allowed_urls', []);
                if (in_array('eligibility-list', $allowed_urls)) { 
                    
                    //check url has permission or not
                        $course_code = $request->course_code;
                        $eligibility_list = Eligibility::where('course_code',$course_code)->where('is_active', '1')->orderBy('id', 'DESC')->get();
                        if (sizeof($eligibility_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Data found',
                                'count'     =>   sizeof($eligibility_list),
                                'eligibilities'    =>  EligibilityResource::collection($eligibility_list)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No data available'
                            );
                            return response(json_encode($reponse), 404);
                        }
                }else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 403);
                }        
                  
            }else{
                $course_code = $request->course_code;
                $eligibility_list = Eligibility::where('course_code',$course_code)->where('is_active', '1')->orderBy('id', 'DESC')->get();
                if (sizeof($eligibility_list) > 0) {
                    $reponse = array(
                        'error'     =>  false,
                        'message'   =>  'Data found',
                        'count'     =>   sizeof($eligibility_list),
                        'eligibilities'    =>  EligibilityResource::collection($eligibility_list)
                    );
                    return response(json_encode($reponse), 200);
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'No data available'
                    );
                    return response(json_encode($reponse), 404);
                }

            }
        }
          
       

    }
    public function eligibilityMatch(Request $request)
    {
        $course_code = $request->course_code;
        $qualification_code = $request->qualification_code;
        $eligibility_match = Eligibility::where('course_code', $course_code)
                ->where('elgb_exam_short_code', $qualification_code)
                ->where('is_active', '1')
                ->exists();
        $is_matched = null;
        if ($course_code == 'ADCS' && $qualification_code == 'GSC') {
                $is_matched = 'PCM';
        } elseif (($course_code == 'ISP' || $course_code == 'ISF') && $qualification_code == 'DSC') {
                $is_matched = 'PCM';
        } elseif ($course_code == 'PDME' && $qualification_code == 'BDSC') {
                $is_matched = 'ALL';
        }
        if ($is_matched) {
                return response()->json([
                    'error'   => false,
                    'message' => 'Match Found',
                    'matched' => $is_matched
                ], 200);
        } 
        return response()->json([
                'error'   => true,
                'message' => 'No Match Found'
         ], 200);
        
            
        
    }
    public function instwiseCenter(Request $request,$inst_id)
    {
        $allowed_urls = $request->get('allowed_urls', []);
        if (in_array('venue-list', $allowed_urls)) { //check url has permission or not
            
            $venue_list = Venue::where('is_active', 1)
                ->where('inst_id',$inst_id)
                ->orderBy('id', 'DESC')
                ->get();
            
            return response()->json([
                'error' => false,
                'message' => 'Data found',
                'count' => sizeof($venue_list),
                'list' => $venue_list
            ]);
            
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>   "Oops! you don't have sufficient permission"
            ], 403);
        }
                   
    
     

    }
 
   
}
