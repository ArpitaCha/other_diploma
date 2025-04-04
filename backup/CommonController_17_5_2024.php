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
use App\Models\wbscte\Institute;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\TheorySubject;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\ExternelExaminerMap;
use App\Http\Resources\wbscte\UserResource;
use App\Http\Resources\wbscte\StateResource;
use App\Http\Resources\wbscte\CourseResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\wbscte\DistrictResource;
use App\Http\Resources\wbscte\InstituteResource;
use App\Http\Resources\wbscte\TheoryPaperResource;
use App\Models\wbscte\OtherDiplomaExaminnerInstitute;


class CommonController extends Controller
{

    public function __construct()
    {
        //$this->auth = new Authentication();
    }
    //State List
    public function allStates(Request $request)
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
                    if (in_array('state-list', $url_data)) { //check url has permission or not
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

    //District List
    public function allDistricts(Request $request, $state_code = null)
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
                            return response(json_encode($reponse), 404);
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

    //Institute List
    public function allInstList(Request $request, $inst_id = null)
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

                    if (in_array('get-all-institute-list', $url_data)) { //check url has permission or not
                        $inst_res = null;
                        $inst_list = Institute::where('inst_sl_pk', '>', 0);
                        if ($inst_id) {
                            $inst_list->where('inst_sl_pk', $inst_id);
                        }
                        $inst_res = $inst_list->orderBy('inst_sl_pk', 'ASC')->get();


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

    //Course List
    public function allCourseList(Request $request, $inst_id = null)
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

                    if (in_array('all-course', $url_data)) { //check url has permission or not
                        $course_res = null;
                        $course_list = Course::where('course_id_pk', '>', 0)->with('institute');
                        if ($inst_id) {
                            $course_list->where('inst_id', $inst_id);
                        }
                        $course_res = $course_list->orderBy('course_id_pk', 'ASC')->get();
                        //return $course_res;

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

    //Theory Paper List
    public function allTheoryPaperList(Request $request)
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

                    if (in_array('all-theory-paper', $url_data)) { //check url has permission or not
                        $paper_res = null;
                        $paper_list = TheorySubject::where('paper_id_pk', '>', 0)->with(['institute', 'course']);
                        $paper_res = $paper_list->orderBy('paper_id_pk', 'ASC')->get();
                        //return $paper_;

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

    //Institute wise Course list 
    public function instwiseCourse(Request $request)
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

                    if (in_array('course-list', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'sessionYear' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $course_list = Course::where('is_active', 1)
                            ->where(['inst_id' => $request->inst_id, 'course_affiliation_year' => $request->sessionYear])
                            ->get();

                        if (sizeof($course_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Institute List found',
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

    //Institute,course,active session,semester,paper type wise paper list
    public function instCourseSessionSemPaperTypewisePaperlist(Request $request)
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

                    if (in_array('paper-list', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'course_id' => ['required'],
                            'semester' => ['required'],
                            'paper_type' => ['required'],
                            'sessionYear' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $paper_list = TheorySubject::where('is_active', 1)->where(['inst_id' => $request->inst_id, 'course_id' => $request->course_id, 'paper_affiliation_year' => $request->sessionYear, 'paper_category' => $request->paper_type, 'paper_semester' => $request->semester])->get();
                        } else if ($user_data->u_role_id == '2') { //Institute Admin
                            $paper_list = TheorySubject::where('is_active', 1)->where(['inst_id' => $request->inst_id, 'course_id' => $request->course_id, 'paper_affiliation_year' => $request->sessionYear, 'paper_category' => $request->paper_type, 'paper_semester' => $request->semester])->get();
                        } else if ($user_data->u_role_id == '3') { // Examiner 
                            // $institute_ids = DB::table('wbscte_other_diploma_examiner_institute_tag_master')->select('examiner_paper_code')->where('examiner_user_id', $user_data->u_id)->where('examiner_course_id', $request->course_id)->where('is_active', 1)->where('')->groupBy('examiner_inst_id')->first('examiner_inst_id');
                            // $institute_list = Institute::where('is_active', 1)->whereIn('inst_sl_pk', $institute_ids)->get();

                            $paper_list = TheorySubject::where('is_active', 1)->where(['inst_id' => $request->inst_id, 'course_id' => $request->course_id, 'paper_affiliation_year' => $request->sessionYear, 'paper_category' => $request->paper_type, 'paper_semester' => $request->semester])->get();
                        }


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

    //Active session 
    public function activeSession(Request $request, $type = null)
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

                    if (in_array('active-session', $url_data)) { //check url has permission or not
                        if (is_null($type)) {
                            throw ValidationException::withMessages(['type' => 'type is required as path param']);
                        }

                        $current_session = DB::table('wbscte_other_diploma_session_tbl')->select('session_name')->where('session_active', 1)->first();

                        $sessions = getFinancialYear($current_session->session_name, $type);

                        if (sizeof($sessions) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Active Session found',
                                'count'         =>   sizeof($sessions),
                                'activeSession'   =>  $sessions,
                                'examYears' => date('Y')
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

    //User wise Institute List
    public function userWiseInstitute(Request $request)
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

                    if (in_array('institute-list', $url_data)) { //check url has permission or not
                        if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $institute_list = Institute::where('is_active', 1)->get();
                        } else if ($user_data->u_role_id == '2') { //Institute Admin
                            $institute_list = Institute::where('is_active', 1)->where('inst_sl_pk', $user_data->u_inst_id)->get();
                        } else if ($user_data->u_role_id == '3') { // Examiner 
                            $institute_ids = DB::table('wbscte_other_diploma_examiner_institute_tag_master')->where('examiner_user_id', $user_data->u_id)->where('is_active', 1)->groupBy('examiner_inst_id')->pluck('examiner_inst_id');

                            $external_inst_ids = DB::table('wbscte_external_examinner_mapping_master_tbl')->where('map_examiner_id', $user_data->u_id)->where('is_active', 1)->groupBy('map_assign_inst_id')->pluck('map_assign_inst_id');

                            //$mergeArr = array_marge()
                            $institute_list = Institute::where('is_active', 1)->whereIn('inst_sl_pk', $institute_ids)->get();
                        }


                        if (sizeof($institute_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Institute List found',
                                'count'         =>   sizeof($institute_list),
                                'list'   =>  InstituteResource::collection($institute_list)
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

    public function examinerList(Request $request)
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

                    if (in_array('examiner-list', $url_data)) { //check url has permission or not
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
    public function editCourse(Request $request, $id)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $token_user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');
                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('edit-course', $url_data)) { //check url has permission or not
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
                        ], 401);
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
    public function updateCourse(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    'course_name'   => 'required',
                    'course_code'    => 'required',
                    'course_duration'       => 'required',
                    'course_type'       => 'required',
                    'course_id'       => 'required',
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

                        if (in_array('update-course', $url_data)) { //check url has permission or not
                            DB::beginTransaction();
                            try {
                                $course = Course::where('course_id_pk', $request->course_id)->first();
                                if ($course) {
                                    $course_id = $course->course_id_pk;
                                    $course->course_type  =  $request->course_type;
                                    $course->course_duration = $request->course_duration;
                                    $course->course_code = $request->course_code;
                                    $course->course_name = $request->course_name;
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
    public function updateInstitute(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    'institute_code'   => 'required',
                    'institute_name'    => 'required',
                    'institute_address'       => 'required',
                    'institute_id'       => 'required',
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

                        if (in_array('update-institute', $url_data)) { //check url has permission or not
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
    public function updatePaper(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    'paper_code'   => 'required',
                    'paper_name'    => 'required',
                    'paper_type'       => 'required',
                    'paper_credit'       => 'required',
                    'paper_full_marks'       => 'required',
                    'paper_internal_marks'       => 'required',
                    'paper_external_marks'       => 'required',
                    'paper_pass_marks'       => 'required',
                    'paper_id'    => 'required'
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

                        if (in_array('update-paper', $url_data)) { //check url has permission or not
                            DB::beginTransaction();
                            try {
                                $paper = TheorySubject::where('paper_id_pk', $request->paper_id)->first();

                                if ($paper) {
                                    $paper_id = $paper->paper_id_pk;
                                    $paper->paper_code  =  $request->paper_code;
                                    $paper->paper_name = $request->paper_name;
                                    $paper->paper_type = $request->paper_type;
                                    $paper->paper_credit = $request->paper_credit;
                                    $paper->paper_full_marks = $request->paper_full_marks;
                                    $paper->paper_internal_marks = $request->paper_internal_marks;
                                    $paper->paper_external_marks = $request->paper_external_marks;
                                    $paper->paper_pass_marks = $request->paper_pass_marks;
                                    $paper->is_active = $request->is_active;
                                    $paper->save();
                                    DB::commit();
                                    return response()->json([
                                        'error'     =>  false,
                                        'message'   =>  'Paper updated successfully'
                                    ], 200);
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

    public function checkPaper(Request $request)
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

    public function semesterList(Request $request)
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

                    if (in_array('semester-list', $url_data)) { //check url has permission or not
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

    public function paperTypeList(Request $request)
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

                    if (in_array('paper-type-list', $url_data)) {
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

                                $int_type = OtherDiplomaExaminnerInstitute::where([
                                    'examiner_user_id' => $user_id,
                                    'examiner_inst_id' => $inst_id,
                                    'examiner_course_id' => $course_id,
                                    'is_active' => 1
                                ])->first('map_paper_type');

                                if ($int_type) {
                                    if ($int_type->map_paper_type == 1) {
                                        if (!searchAssociative($paper_types, 'name', $type1)) {
                                            array_push($paper_types, [
                                                'name' => $type1,
                                                'value' => 1
                                            ]);
                                        }
                                    } else if ($int_type->map_paper_type == 2) {
                                        if (!searchAssociative($paper_types, 'name', $type2)) {
                                            array_push($paper_types, [
                                                'name' => $type2,
                                                'value' => 2
                                            ]);
                                        }
                                    }
                                }

                                $ext_type = ExternelExaminerMap::where([
                                    'map_examiner_id' => $user_id,
                                    'map_assign_inst_id' => $inst_id,
                                    'map_course_id' => $course_id,
                                    'is_active' => 1
                                ])->first('map_paper_type');

                                if ($ext_type) {
                                    if ($ext_type->map_paper_type == 1) {
                                        if (!searchAssociative($paper_types, 'name', $type1)) {
                                            array_push($paper_types, [
                                                'name' => $type1,
                                                'value' => 1
                                            ]);
                                        }
                                    } else if ($ext_type->map_paper_type == 2) {
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

    public function entryTypeList(Request $request)
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

                    if (in_array('entry-type-list', $url_data)) {
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
                                // Examiner

                                $entry_types = [];

                                $int_type = OtherDiplomaExaminnerInstitute::where([
                                    'examiner_user_id' => $user_id,
                                    'examiner_inst_id' => $inst_id,
                                    'examiner_course_id' => $course_id,
                                    'is_active' => 1
                                ])->first('map_paper_entry_type');

                                if ($int_type) {
                                    if ($int_type->map_paper_entry_type == 1) {
                                        if (!searchAssociative($entry_types, 'name', $type1)) {
                                            array_push($entry_types, [
                                                'name' => $type1,
                                                'value' => 1
                                            ]);
                                        }
                                    } else if ($int_type->map_paper_entry_type == 2) {
                                        if (!searchAssociative($entry_types, 'name', $type2)) {
                                            array_push($entry_types, [
                                                'name' => $type2,
                                                'value' => 2
                                            ]);
                                        }
                                    }
                                }

                                $ext_type = ExternelExaminerMap::where([
                                    'map_examiner_id' => $user_id,
                                    'map_assign_inst_id' => $inst_id,
                                    'map_course_id' => $course_id,
                                    'is_active' => 1
                                ])->first('map_paper_entry_type');

                                if ($ext_type) {
                                    if ($ext_type->map_paper_entry_type == 1) {
                                        if (!searchAssociative($entry_types, 'name', $type1)) {
                                            array_push($entry_types, [
                                                'name' => $type1,
                                                'value' => 1
                                            ]);
                                        }
                                    } else if ($ext_type->map_paper_entry_type == 2) {
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
