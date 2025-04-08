<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use Illuminate\Support\Str;
use App\Models\wbscte\Token;
use App\Models\wbscte\Trade;
use Illuminate\Http\Request;
use App\Models\wbscte\Payment;
use Illuminate\Support\Carbon;
use App\Models\wbscte\District;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\wbscte\Institute;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\StudentChoice;
use App\Models\wbscte\StudentActivity;
use App\Models\wbscte\PaymentTransaction;
use App\Models\wbscte\SpotStudent;
use App\Models\wbscte\SpotStudentAllotment;
use App\Models\wbscte\SpotStudentChoice;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\Schedule;
use App\Http\Resources\wbscte\StudentChoiceResource;
use App\Http\Resources\wbscte\SpotStudentChoiceResource;
use App\Http\Resources\wbscte\StudentActivityResource;

class StudentController extends Controller
{

    protected $auth;
    public $back_url = null;

    public function __construct()
    {
        //$this->auth = new Authentication();
    }

    public function saveProfileDistrict(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::where('s_id', $user_id)->where('is_active', 1)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('profile-district-save', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'home_district' => ['required'],
                            'school_district' => ['required']
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $home_district = $request->home_district;
                            $school_district = $request->school_district;
                            $other_state_id = $request->state_id;
                            //dd($request->all());

                            $school_districtData = District::where('d_id', $school_district)->first('d_name');
                            $home_districtData = District::where('d_id', $home_district)->first('d_name');
                            //$school_district_name  = $school_districtData->d_name;

                            $rankArr = array('s_gen_rank', 's_sc_rank', 's_st_rank', 's_obca_rank', 's_obcb_rank', 's_pwd_rank', 's_tfw_rank', 's_ews_rank', 's_llq_rank', 's_exsm_rank');
                            $rank_data = [];
                            $userRank = $user_data;
                            foreach ($rankArr as $val) {
                                $userRankData = (int)$userRank[$val];
                                if (!is_null($userRankData) && ($userRankData != 0)) {

                                    $cat = explode('_', $val);
                                    array_push(
                                        $rank_data,
                                        [
                                            'category' => casteValue(Str::upper($cat[1])),
                                            'rank' => $userRankData
                                        ]
                                    );
                                }
                            }

                            if ($home_district == '99' && $school_district == '99') {

                                if (is_null($other_state_id)) {
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>   "Oops! State id is required"
                                    ], 200);
                                } else {
                                    $new_user = User::updateOrCreate([
                                        's_id'   => $user_id,
                                    ], [
                                        's_home_district' => $home_district,
                                        's_schooling_district' => $school_district,
                                        's_state_id' => $other_state_id,
                                        'updated_at' => $now,
                                    ]);
                                }
                            } else {

                                $new_user = User::updateOrCreate([
                                    's_id'   => $user_id,
                                ], [
                                    's_home_district' => $home_district,
                                    's_schooling_district' => $school_district,
                                    'updated_at' => $now,
                                ]);
                            }

                            $users =  [
                                's_id' => $user_data->s_id,
                                's_index_num' => $user_data->s_index_num,
                                's_appl_form_num' => $user_data->s_appl_form_num,
                                's_first_name' => $user_data->s_first_name,
                                's_middle_name' => $user_data->s_middle_name,
                                's_last_name' => $user_data->s_last_name,
                                's_full_name' => $user_data->s_candidate_name,
                                's_father_name' => $user_data->s_father_name,
                                's_mother_name' => $user_data->s_mother_name,
                                's_dob' => $user_data->s_dob,
                                's_aadhar_no' => $user_data->s_aadhar_no,
                                's_phone' => $user_data->s_phone,
                                's_email' => $user_data->s_email,
                                's_gender' => $user_data->s_gender,
                                's_religion' => $user_data->s_religion,
                                's_caste' => $user_data->s_caste,
                                's_tfw' => $user_data->s_tfw,
                                's_pwd' => $user_data->s_pwd,
                                's_llq' => $user_data->s_llq,
                                's_exsm' => $user_data->s_exsm,
                                's_alloted_category' => $user_data->s_alloted_category,
                                's_alloted_round' => $user_data->s_alloted_round,
                                's_choice_id' => $user_data->s_choice_id,
                                's_trade_code' => $user_data->s_trade_code,
                                's_inst_code' => $user_data->s_inst_code,
                                'is_alloted' => $user_data->is_alloted,
                                'is_choice_fill_up' => $user_data->is_choice_fill_up,
                                'is_payment' => $user_data->is_payment,
                                'is_upgrade' => $user_data->is_upgrade,
                                's_photo' => $user_data->s_photo,
                                's_home_district' => $new_user->s_home_district,
                                's_schooling_district' => $new_user->s_schooling_district,
                                's_state_id' => !is_null($other_state_id) ? $other_state_id : $user_data->s_state_id,
                                'is_active' => $user_data->is_active,
                                'is_lock_manual' => $user_data->is_lock_manual,
                                'is_lock_auto' => $user_data->is_lock_auto,
                                'created_at' => $user_data->created_at,
                                'updated_at' => $user_data->updated_at,
                                'manual_lock_at' => $user_data->manual_lock_at,
                                'auto_lock_at' => $user_data->auto_lock_at,
                                'rank' => $rank_data,
                            ];

                            $student_name = $user_data->s_candidate_name;
                            $std_activity   =   "{$student_name} updated home district as {$home_districtData->d_name} and schooling district as {$school_districtData->d_name}";

                            auditTrail($user_id, $std_activity);
                            studentActivite($user_id, $std_activity);

                            DB::commit();

                            $redirect = $this->checkRedirect($user_id);


                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Student profile successfully updated",
                                'userData'              =>  json_encode($users),
                                'redirect' => $redirect
                            ], 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Student choice entry
    public function choiceEntry(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-entry', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'trade_code' => ['required'],
                            'inst_code' => ['required'],
                            'choice_id' => ['nullable'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $trade_code = $request->trade_code;
                            $inst_code = $request->inst_code;
                            $choice_id = $request->choice_id;
                            $old_choice_pref_no = 1;

                            $tradeData = Trade::where(['t_code' => $trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $inst_code, 'is_active' => 1])->first();

                            $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                            $checkExists = StudentChoice::where('ch_stu_id', $user_id)->where('ch_trade_code', $trade_code)->where('ch_inst_code', $inst_code)->first();

                            if ($checkExists) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists with the same choice, please try other!"
                                ], 200);
                            } else {
                                $choice_cnt = StudentChoice::where('ch_stu_id', $user_id)->count();
                                //return $choice_cnt;
                                $choice_pref_no = $choice_cnt + 1;
                                $student_name = $user_data->s_candidate_name;

                                if (!is_null($choice_id)) {
                                    $old_choice_pref = StudentChoice::where('ch_un_id', $choice_id)->first();

                                    if ($old_choice_pref) {
                                        $old_choice_pref_no = $old_choice_pref->ch_pref_no;
                                    }
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;

                                    $old_choice_pref->update([
                                        'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice updated successfully";
                                } else {
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;
                                    StudentChoice::create([
                                        'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                        'ch_fillup_time' => now()
                                    ]);

                                    $message = "{$student_name} selected choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice created successfully";
                                }

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  $resp_msg
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Student choice list
    public function choiceList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id', 'is_lock_manual', 'is_lock_auto')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-list', $url_data)) { //check url has permission or not
                        $choice_res = null;
                        $choice_list = StudentChoice::where('ch_stu_id',  $user_id)->where('ch_fillup_time', '>', '2024-07-14 11:00:00')->with('student')->orderBy('ch_pref_no', 'ASC')->get();

                        if (sizeof($choice_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student choice found',
                                'count'     =>   sizeof($choice_list),
                                'choiceList'   =>  StudentChoiceResource::collection($choice_list)
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

    //Student choice remove
    public function choiceRemove(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-remove', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;

                            $oldData = StudentChoice::where('ch_un_id', $choice_id)->first();
                            $old_pref_no = $oldData->ch_pref_no;
                            $old_trade_code = $oldData->ch_trade_code;
                            $old_inst_code = $oldData->ch_inst_code;

                            $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                            $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                            $del = $oldData->delete();
                            //dd($del);
                            if ($del == 1) {
                                //dd($old_pref_no);
                                $list = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', '>', $old_pref_no)->get();
                                //return $list;
                                if (sizeof($list) > 0) {
                                    foreach ($list as $key => $val) {
                                        $val->update([
                                            'ch_pref_no' => (int)$val->ch_pref_no - 1,
                                        ]);
                                    }
                                }


                                $student_name = $user_data->s_candidate_name;

                                $message = "{$student_name} removed choice #{$old_pref_no} having [{$old_inst_code}] - {$inst_name} and [{$old_trade_code}]  -  {$trade_name}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Choice removed successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Student choice does not exist"
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Student up/down choice
    public function choiceUpDown(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-up-down', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                            'type' => ['required'],
                            'old_preference_no' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;
                            $type = $request->type;
                            $old_choice_pref_no = (int)$request->old_preference_no;
                            //$new_choice_pref_no = "";
                            $student_name = $user_data->s_candidate_name;

                            if ($type == 'up') {
                                $new_choice_pref_no = (int)($old_choice_pref_no - 1);
                                $existingData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();

                                

                                $toData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                $old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;


                                $to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();


                                $totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                                $to_trade_name = !is_null($totradeData->t_name) ? $totradeData->t_name : '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';


                                if ($existingData) {
                                    StudentChoice::where('ch_un_id', $existingData->ch_un_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$old_choice_pref_no} to #{$new_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                StudentChoice::where('ch_un_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                // $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                // auditTrail($user_id, $message);
                                // studentActivite($user_id, $message);
                            } else {
                                $new_choice_pref_no = (int)($old_choice_pref_no + 1);
                                $existingData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();
                                //dd($existingData);
                                $toData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                $old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;

                                $to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                                $totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                                $to_trade_name = !is_null($totradeData->t_name) ? $totradeData->t_name : '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';

                                if ($existingData) {
                                    StudentChoice::where('ch_un_id', $existingData->ch_un_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                StudentChoice::where('ch_un_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                /* $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message); */
                            }

                            DB::commit();
                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Choice updated successfully"
                            ], 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Student bulk final submit
    public function choiceLockFinalSubmit(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-lock-final-submit', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            User::where('s_id', $user_id)->update([
                                'is_lock_manual' => 1,
                                'is_choice_fill_up' => 1,
                                'manual_lock_at' => now()
                            ]);
                            $student_name = $user_data->s_candidate_name;
                            $redirect = $this->checkRedirect($user_id);

                            $message = "{$student_name} submitted and locked choices";

                            auditTrail($user_id, $message);
                            studentActivite($user_id, $message);

                            DB::commit();
                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  "Choices are successfully locked and submitted",
                                'redirect'  =>  $redirect
                            ], 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Student Activities
    public function activities(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id', 'is_lock_manual', 'is_lock_auto')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('activities', $url_data)) { //check url has permission or not
                        $activity_res = null;
                        $activity_list = StudentActivity::where('a_stu_id',  $user_id);
                        $activity_res = $activity_list->orderBy('a_id', 'ASC')->get();


                        if (sizeof($activity_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student activities found',
                                'count'     =>   sizeof($activity_res),
                                'activityList'   =>  StudentActivityResource::collection($activity_res)
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

    //Choice fill up pdf
    public function choicePdf(Request $request)
    {
        $student_id = $request->student_id;
        //dd($request->all());->with(['trade:t_code,t_name', 'institute:i_code,i_name', 'student:s_id,s_candidate_name,s_phone'])

        $choice_list = StudentChoice::select('ch_id', 'ch_trade_code', 'ch_inst_code', 'ch_pref_no', 'ch_stu_id', 'ch_fillup_time')->where('ch_stu_id',  $student_id)->where('ch_fillup_time', '>', '2024-07-14 11:00:00')->with(['trade:t_code,t_name', 'institute:i_code,i_name', 'student:s_id,s_candidate_name,s_phone'])->orderBy('ch_pref_no', 'ASC')->get();
        //return $choice_list;

        $finalList = $choice_list->map(function ($single, $key) {
            return [
                'choice_time' => $single->ch_fillup_time,
                'choice_no' => $single->ch_pref_no,
                'institute_name' => $single->institute->i_name,
                'branch_name' => $single->trade->t_name,
                'branch_code' => $single->trade->t_code,
                'institute_code' => $single->institute->i_code,
                'candidate_name' => $single->student->s_candidate_name,
                'candidate_phone' => $single->student->s_phone,
            ];
        });

        //return $finalList;

        $pdf = Pdf::loadView('exports.choicefill', [
            'choices' => $finalList,
        ]);
        return $pdf->setPaper('a4', 'portrait')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('choice.pdf');
    }

    //Alotement Details
    public function allotementDetails(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('allotement-details', $url_data)) { //check url has permission or not
                        if ($user_data) {
                            $choiceData = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->first();

                            if ($choiceData) {
                                $institute = Institute::where('i_code', $choiceData->ch_inst_code)->first();
                                $branch = Trade::where('t_code', $choiceData->ch_trade_code)->first();
                                $choice = $choiceData->ch_pref_no;
                                $user = [
                                    'institute_name' =>  $institute->i_name,
                                    'branch_name' =>  $branch->t_name,
                                    'allotement_category' =>  !empty($choiceData->ch_alloted_category) ? casteValue(Str::upper($choiceData->ch_alloted_category)) : "N/A",
                                    'allotement_round' =>  !empty($choiceData->ch_alloted_round) ? $choiceData->ch_alloted_round : "N/A",
                                    'choice_option' =>  $choice,
                                    'allotment_accepted' => (bool)$user_data->is_allotment_accept
                                ];

                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'Data found',
                                    'allotementDetails'   =>  $user
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'details_found' => !is_null($choiceData) ? true : false,
                                    'message'   =>  'Sorry allotement is not done yet'
                                );
                                return response(json_encode($reponse), 200);
                            }
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No user found!'
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

    public function checkRedirect($user_id)
    {
        $newuser = User::where('s_id', $user_id)->first();
        //return $newuser;

        if ($newuser) {
            $profile_update = $choice_fillup_page = $payment_page = $allotement_page = $choice_preview_page = false;
            $payment_done =  $upgrade_done = $admitted = $accept_allotement = $upgrade_payment_done = $reject =  $schedule_choice_fillup = $schedule_admission = $student_auto_reject = false;

            $checkChoice = $newuser->is_lock_manual;
            $checkChoiceAuto = $newuser->is_lock_auto;
            $checkPayment = $newuser->is_payment;
            $checkallotement = $newuser->is_alloted;
            $checkUpgrade = $newuser->is_upgrade;
            $checkUpgradePayment = $newuser->is_upgrade_payment;
            $checkAdmitted = $newuser->s_admited_status;
            $checkAllotementAccept = $newuser->is_allotment_accept;
            $checkStatusReject = $newuser->s_admited_status;
            $student_reject_remarks = $newuser->s_remarks;
            
			$check_choice_fillup = config_schedule('CHOICE_FILLUP');
            $check_choice_status = $check_choice_fillup['status'];
			
			$check_accept = config_schedule('ACCEPT');
            $check_accept_status = $check_accept['status'];
			
			$check_upgrade = config_schedule('UPGRADE');
            $check_upgrade_status = $check_upgrade['status'];
			
            $check_admission = config_schedule('ADMISSION');
            $check_admission_status = $check_admission['status'];
			
            $checkStudentAutoRejectRound = $newuser->s_auto_reject;

            //dd($checkPayment);

            if (!is_null($newuser->s_home_district) && !is_null($newuser->s_schooling_district)) {
                $profile_update = true;
            }

            if (!is_null($newuser->s_home_district) && !is_null($newuser->s_schooling_district) && (($checkChoice == 0) &&  ($checkChoiceAuto == 0))) {
                $choice_fillup_page = true;
            }

            if (!is_null($newuser->s_home_district) && !is_null($newuser->s_schooling_district) && (($checkChoice == 1) ||  ($checkChoiceAuto == 1)) && ($checkallotement == 0)) { //&& ($checkallotement == 0)
                $choice_preview_page = true;
            }

            if (!is_null($newuser->s_home_district) && !is_null($newuser->s_schooling_district) && (($checkChoice == 1) || ($checkChoiceAuto == 1)) && ($checkPayment == 0)) {
                $payment_page = true;
            }

            if (!is_null($newuser->s_home_district) && !is_null($newuser->s_schooling_district) && (($checkChoice == 1) || ($checkChoiceAuto == 1)) && ($checkallotement == 1)) {
                $allotement_page = true; // && ($checkPayment == 1)
            }

            if ($checkPayment == 1) {
                $payment_done = true;
            }
            if ($checkUpgrade == 1) {
                $upgrade_done = true;
            }
            if ($checkUpgradePayment == 1) {
                $upgrade_payment_done = true;
            }
            if ($checkAdmitted == 1) {
                $admitted = true;
            }
            if ($checkAllotementAccept == 1) {
                $accept_allotement = true;
            }
            if ($checkStatusReject == 2) {
                $reject = true;
            }
            if ($check_choice_status == true) {
                $schedule_choice_fillup = true;
            }
            if ($check_admission_status == true) {
                $schedule_admission = true;
            }
            if ($checkStudentAutoRejectRound == 1) {
                $student_auto_reject = true;
            }

            $redirect = [
                'profile_update'   => $profile_update,
                'choice_fillup_page'   => $choice_fillup_page,
                'payment_page'   => $payment_page,
                'allotement_page'   => $allotement_page,
                'choice_preview_page'   => $choice_preview_page,
                'payment_done' => $payment_done,
                'upgrade_done' => $upgrade_done,
                'upgrade_payment_done' => $upgrade_payment_done,
                'student_admitted' => $admitted,
                'student_allotment_accepted' => $accept_allotement,
				'allotment_accepted' => $accept_allotement,
                'student_reject_status' => $reject,
                'student_reject_remarks' => $student_reject_remarks,
				
                'schedule_choice_fillup' => $schedule_choice_fillup,
				'schedule_acceptance' => $check_accept_status,
				'schedule_upgradation' => $check_upgrade_status,
                'schedule_admission' => $schedule_admission,
				
                'student_auto_reject' => $student_auto_reject,
				'overall_status' => getOverallStatus($newuser->s_id)
            ];
            if (request()->segment(3) == 'check-payment') {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Data found',
                    'redirect' => $redirect
                );
                return response(json_encode($reponse), 200);
            } else {
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'Data found',
                    'redirect' => $redirect
                );
                return response(json_encode($reponse), 200);
                //return $redirect;
            }
        }
    }

    //Allotement Pdf
    public function allotmentPdf(Request $request)
    {
        $student_uuid = $request->student_id;
        if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $student_uuid) == 1) {
            $userData = User::where('s_uuid', $student_uuid)->first();
            //return $userData;
            if ($userData) {
                $student_id = $userData->s_id;
                $option_type = $request->type;
                $trans_time = $trans_amount =  $trans_mode = $transactionId = $amount_in_words = $bank_ref_no =  '';
                //dd($option_type);
                //->where('sch_start_dt', '<=', $time)->where('sch_end_dt', '>=', $time)
                $year = date('Y');
                //dd(sessionYear($year));
                $time = date('Y-m-d H:i:s');

                $event = 'ADMISSION';
                $configSchedule = Schedule::where('sch_event', $event)->where('sch_round', 4)->first();
                $end_date = '';
                if ($configSchedule) {
                    $dateStr = explode(' ', $configSchedule->sch_end_dt);
                    $end_date = $dateStr[0];
                }

                $choice_list = StudentChoice::where('ch_stu_id', $student_id)->where('is_alloted', 1)->with('student')->first();
                $checkPayment = PaymentTransaction::where('pmnt_modified_by', $student_id)->whereNotNull('trans_amount')->orderBy('trans_amount', 'ASC')->get()->toArray();
                //return $checkPayment;

                if (sizeof($checkPayment) > 0) {
                    if (is_null($option_type)) {
                        $transactionId = $checkPayment[0]['trans_id'];
                        $amount_in_words = 'Rupees Five Hundred';
                        $trans_time = $checkPayment[0]['trans_time'] ?? '';
                        $trans_amount = $checkPayment[0]['trans_amount'] ?? '';
                        $trans_mode = $checkPayment[0]['trans_mode'] ?? '';
                        $bank_ref_no = $checkPayment[0]['bank_ref'] ?? '';
                    } else {
                        $transactionId = isset($checkPayment[1]['trans_id']) ? $checkPayment[1]['trans_id'] : '';
                        $amount_in_words = 'Rupees One Thousand';
                        $trans_time = $checkPayment[1]['trans_time'] ?? '';
                        $trans_amount = $checkPayment[1]['trans_amount'] ?? '';
                        $trans_mode = $checkPayment[1]['trans_mode'] ?? '';
                        $bank_ref_no = $checkPayment[1]['bank_ref'] ?? '';
                    }
                }
                //return $choice_list;
                //dd($trans_time);
                if ($choice_list) {
                    $institute = Institute::where('i_code', $choice_list->ch_inst_code)->first();


                    $con_person1 = !is_null($institute->i_contact_person_name_1) ? Str::upper($institute->i_contact_person_name_1) : 'N/A';
                    $con_person2 = !is_null($institute->i_contact_person_name_2) ? Str::upper($institute->i_contact_person_name_2) : 'N/A';
                    $con_person3 = !is_null($institute->i_contact_person_name_3) ? Str::upper($institute->i_contact_person_name_3) : 'N/A';

                    $con_person_ph1 = !is_null($institute->i_contact_person_phone_1) ? $institute->i_contact_person_phone_1 : 'N/A';
                    $con_person_ph2 = !is_null($institute->i_contact_person_phone_2) ? $institute->i_contact_person_phone_2 : 'N/A';
                    $con_person_ph3 = !is_null($institute->i_contact_person_phone_3) ? $institute->i_contact_person_phone_3 : 'N/A';
                    $contact_persons = $con_person1 . ',' . $con_person2 . ',' . $con_person3;
                    $contact_person_phones = $con_person_ph1 . ',' . $con_person_ph2 . ',' . $con_person_ph3;
                    $contact_inst_email = !is_null($institute->i_contact_email) ? $institute->i_contact_email : 'N/A';

                    //dd($contact_persons, $contact_person_phones);

                    $branch = Trade::where('t_code', $choice_list->ch_trade_code)->first();
                    $choice = $choice_list->ch_pref_no;
                    $rankArr = array('s_gen_rank', 's_sc_rank', 's_st_rank', 's_obca_rank', 's_obcb_rank', 's_pwd_rank');
                    $rank_data = [];
                    $userRank = $choice_list->student;
                    $genRank = $choice_list->student->s_gen_rank;

                    $school_districtData = District::where('d_id',  $choice_list->student->s_schooling_district)->first('d_name');
                    $home_districtData = District::where('d_id',  $choice_list->student->s_home_district)->first('d_name');
                    foreach ($rankArr as $val) {
                        $userRankData = (int)$userRank[$val];
                        //if (!is_null($userRankData) && ($userRankData != 0)) {

                        $cat = explode('_', $val);
                        array_push(
                            $rank_data,
                            [
                                'category' => casteValue(Str::upper($cat[1])),
                                'rank' => $userRankData
                            ]
                        );
                        //}
                    }

                    $finalList = [
                        'institute_name' =>  $institute->i_name,
                        'branch_name' =>  $branch->t_name,
                        'contact_person_name' =>  $contact_persons,
                        'contact_person_phone' =>  $contact_person_phones,
                        'contact_inst_email' =>  $contact_inst_email,
                        'allotement_category' =>  !empty($choice_list->ch_alloted_category) ? casteValue(Str::upper($choice_list->ch_alloted_category)) : "N/A",
                        'allotement_round' =>  $choice_list->ch_alloted_round,
                        'choice_option' =>  $choice,
                        //'index_num' => $choice_list->student->s_index_num,
                        'appl_form_num' => $choice_list->student->s_appl_form_num,
                        'candidate_gender' => $choice_list->student->s_gender,
                        'candidate_name' => Str::upper($choice_list->student->s_candidate_name),
                        'candidate_guardian_name' => !is_null($choice_list->student->s_father_name) ? Str::upper($choice_list->student->s_father_name) : "N/A",
                        'candidate_caste' => $choice_list->student->s_caste,
                        'candidate_physically_challenged' => ($choice_list->student->s_pwd == 0) ? "No" : "Yes",
                        'candidate_home_district' => $home_districtData->d_name,
                        'candidate_schooling_district' => $school_districtData->d_name,
                        'rank' => $rank_data,
                        'candidate_photo' => $choice_list->student->s_photo,
                        'candidate_sign' => $choice_list->student->s_sign,
                        'candidate_dob' => Carbon::parse($choice_list->student->s_dob)->format('d/m/Y'),
                        'provisional' => !is_null($option_type) ? Str::upper($option_type) : "",
                        'trans_time' => !empty($trans_time) ? Carbon::parse($trans_time)->format('d/m/Y H:i:s') : '',
                        'trans_amount' => $trans_amount,
                        'trans_mode' => $trans_mode,
                        'trans_id' => $transactionId,
                        'gen_rank' => $genRank,
                        'candidate_land_looser' => ($choice_list->student->s_llq == 0) ? "No" : "Yes",
                        'candidate_under_tfw' => ($choice_list->student->s_tfw == 0) ? "No" : "Yes",
                        'candidate_ex_serviceman' => ($choice_list->student->s_exsm == 0) ? "No" : "Yes",
                        'candidate_ews' => ($choice_list->student->s_ews == 0) ? "No" : "Yes",
                        'session' => sessionYear($year),
                        'candidate_phone' => $choice_list->student->s_phone,
                        'admission_end_date' => Carbon::parse($end_date)->format('d/m/Y'),
                        'bank_ref_no' => $bank_ref_no,
                        'amount_in_words' => $amount_in_words
                    ];

                    //return $finalList;

                    $student_name = Str::upper($choice_list->student->s_candidate_name);
                    $message = "{$student_name} downloaded the final allotment letter";

                    auditTrail($student_id, $message);
                    studentActivite($student_id, $message);
                    if (is_null($option_type)) {
                        $pdf = Pdf::loadView('exports.allotment', [
                            'data' => $finalList,
                        ]);
                        return $pdf->setPaper('a4', 'portrait')
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->stream('allotment.pdf');
                    } else {
                        $pdf = Pdf::loadView('exports.allotment-provissional', [
                            'data' => $finalList,
                        ]);
                        return $pdf->setPaper('a4', 'portrait')
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->stream('allotment.pdf');
                    }
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'No data found!'
                    );
                    return response(json_encode($reponse), 200);
                }
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No data found!'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'Invalid Request!'
            );
            return response(json_encode($reponse), 200);
        }
    }

    //Accept allotment
    public function allotmentAccept(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('accept-allotment', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            $choice_list = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->with('student')->first();

                            if ($choice_list) {
                                //dd($old_pref_no);
                                $inst_code = $choice_list->ch_inst_code;
                                $trade_code = $choice_list->ch_trade_code;
                                $choice_pref_no = $choice_list->ch_pref_no;
                                $allotment_category = $choice_list->ch_alloted_category;
                                $alloted_round = $choice_list->ch_alloted_round;
                                $choice_id = $choice_list->ch_un_id;

                                $user_data->update([
                                    'updated_at' => now(),
                                    //'is_alloted' => 1,
                                    's_inst_code' => $inst_code,
                                    's_trade_code' => $trade_code,
                                    's_choice_id' => $choice_id,
                                    's_alloted_round' => $alloted_round,
                                    's_alloted_category' => $allotment_category,
                                    'is_allotment_accept' => 1,
                                ]);

                                $student_name = $user_data->s_candidate_name;
                                $message = "{$student_name} accepted the allotment having Institute Code [{$inst_code}] and Stream Code [{$trade_code}] against the Choice #{$choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Allotment Updated Successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'details_found' => !is_null($choice_list) ? true : false,
                                    'message'              =>  "Choice List Not Found"
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //allotment upgrade 
    public function allotmentUpgrade(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('accept-allotment', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            $choice_list = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->with('student')->first();

                            if ($choice_list) {
                                //dd($old_pref_no);
                                $inst_code = $choice_list->ch_inst_code;
                                $trade_code = $choice_list->ch_trade_code;
                                $choice_pref_no = $choice_list->ch_pref_no;
                                $allotment_category = $choice_list->ch_alloted_category;
                                $alloted_round = $choice_list->ch_alloted_round;
                                $choice_id = $choice_list->ch_un_id;

                                $choice_list->update([
                                    'ch_auto_upgrade' => 1
                                ]);

                                $user_data->update([
                                    'updated_at' => now(),
                                    's_inst_code' => $inst_code,
                                    's_trade_code' => $trade_code,
                                    's_choice_id' => $choice_id,
                                    's_alloted_round' => $alloted_round,
                                    's_alloted_category' => $allotment_category,
                                    'is_upgrade' => 1
                                ]);

                                $student_name = $user_data->s_candidate_name;
                                $message = "{$student_name} upgraded the allotment having Institute Code [{$inst_code}] and Stream Code [{$trade_code}] against the Choice #{$choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Allotment upgraded Successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'details_found' => !is_null($choice_list) ? true : false,
                                    'message'              =>  "Choice List Not Found"
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //spot counselling get info
    public function getSpotStudentInfo(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;

                $validator = Validator::make($request->all(), [
                    'stud_aadhar'   =>  'required|digits:12',
                ],[
                    'stud_aadhar.required' => 'Aadhaar no is required',
                    'stud_aadhar.digits' => 'Aadhaar no must be 12 digits'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages()
                    ], 422);
                }
                //return $user_id;
                //$user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-entry', $url_data)) { //check url has permission or not
                        try {
                            $random = env('ENC_KEY');
                            $enc_aadhaar_num      =   encryptHEXFormat($request->stud_aadhar, $random);

                            //check exists
                            $check_student = SpotStudent::where('s_id',$user_id)->where('s_aadhar_no', $enc_aadhaar_num)->where('is_active', 1)->first();
                            //dd($check_student);where('s_aadhar_no', $enc_aadhaar_num)->
                            $status = $msg = "";
                            if ($check_student != null) {//Old data

                                $check_alloted_status =  SpotStudentAllotment::where(['stu_id' => $check_student->s_id,'alloted_status'=>1])->first();

                                $check_student_register = SpotStudentAllotment::where(['stu_id' => $check_student->s_id])->whereDate('reg_dt', date('Y-m-d'))->first();

                                if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_alloted_status == null && $check_student_register == null){
                                    $status = "COUNSELLING_FEES_PAID_PERCENTAGE_UPDATED";
                                    $msg = "Counselling fees paid successfully";
                                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0){
                                    $status = "COUNSELLING_FEES_NOT_PAID_PERCENTAGE_UPDATED";
                                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0){
                                    $status = "COUNSELLING_FEES_NOT_PAID_PERCENTAGE_NOT_UPDATED";
                                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1){
                                    $status = "COUNSELLING_FEES_PAID_PERCENTAGE_NOT_UPDATED";
                                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_alloted_status != null){
                                    $status = "ALREADY_ALLOTED";
                                    $msg = "You are already alloted";
                                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_alloted_status == null && $check_student_register!= null){
                                    $status = "REGISTERED_FOR_ALLOTEMENT";
                                    $msg = "You are registered for today's allotement";
                                }
                            }else{
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Aadhar no is not matched for the student!'
                                ], 200);
                                
                            }

                            $data = [
                                'student_id' => isset($check_student->s_id)?$check_student->s_id:'',
                                'student_first_name' => isset($check_student->s_first_name)?$check_student->s_first_name:'',
                                'student_middle_name' => isset($check_student->s_middle_name)?$check_student->s_middle_name:'',
                                'student_last_name' => isset($check_student->s_last_name)?$check_student->s_last_name:'',
                                'student_father_name' => isset($check_student->s_father_name)?$check_student->s_father_name:'',
                                'student_mother_name' => isset($check_student->s_mother_name)?$check_student->s_mother_name:'',
                                'student_dob' => isset($check_student->s_dob)?$check_student->s_dob:'',
                                'student_phone' => isset($check_student->s_phone)?$check_student->s_phone:'',
                                'student_email' => isset($check_student->s_email)?$check_student->s_email:'',
                                'student_gender' => isset($check_student->s_gender)?$check_student->s_gender:'',
                                'student_religion' => isset($check_student->s_religion)?$check_student->s_religion:'',
                                'student_caste' => isset($check_student->s_caste)?$check_student->s_caste:'',
                                'student_tfw' => isset($check_student->s_tfw)?$check_student->s_tfw:'',
                                'student_overall_status' => $status,
                                'student_last_exam_percentage' => isset($check_student->last_qualified_exam_percentage)?$check_student->last_qualified_exam_percentage:'',
                                'student_math_percentage' => isset($check_student->math_percentage)?$check_student->math_percentage:'',
                                'student_science_or_physics_percentage' => isset($check_student->physics_or_science_percentage)?$check_student->physics_or_science_percentage:'',
                                'msg' => $msg,
                            ];

                           
                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  'Data fetched successfully',
                                'data'  =>  $data
                            ], 200);
                        } catch (Exception $e) {
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

    public function spotStudentInfoUpdate(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;

                $validator = Validator::make($request->all(), [
                    'student_first_name' => 'required',
                    'student_middle_name' => 'nullable',
                    'student_last_name' => 'required',
                    'student_father_name' => 'required',
                    'student_mother_name' => 'required',
                    'student_dob' => 'required',
                    'student_phone' => 'required|min:10|int',
                    'student_email' => 'required|email',
                    'student_gender' => 'required',
                    'student_religion' => 'required',
                    'student_caste' => 'required',
                    'student_tfw' => 'required',
                    'student_last_exam_percentage' => 'required',
                    'student_math_percentage' => 'required',
                    'student_science_or_physics_percentage' => 'required',
                    'student_id' => 'required',
                    'institute_code' => 'required',
                ],[
                    'student_first_name.required' => 'Student first name is required',
                    'student_last_name.required' => 'Student last name is required',
                    'student_father_name.required' => "Student father's name is required",
                    'student_mother_name.required' => "Student mother's name is required",
                    'student_dob.required' => 'Student date of birth is required',
                    'student_phone.required' => 'Student phone is required',
                    'student_email.required' => 'Student email is required',
                    'student_gender.required' => 'Student gender is required',
                    'student_religion.required' => 'Student religion is required',
                    'student_caste.required' => 'Student caste is required',
                    'student_tfw.required' => 'Student tfw is required',
                    'student_last_exam_percentage.required' => 'Student last exam percentage is required',
                    'student_math_percentage.required' => 'Student math percentage is required',
                    'student_science_or_physics_percentage.required' => 'Student science or physics percentage is required',
                    'student_email.email' => 'Valid email is required',
                    'student_id.required' => 'Student id required',
                    'institute_code.required' => 'Institute is required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages() //$validator->messages()
                    ], 422);
                }
                //return $user_id;
                //$user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    //$inst_code = $request->inst_code;

                    if (in_array('choice-entry', $url_data)) { //check url has permission or not
                        try {
                            // $random = env('ENC_KEY');
                            // $enc_aadhaar_num      =   encryptHEXFormat($request->stud_aadhar, $random);
                            $student_first_name  = Str::upper($request->student_first_name);
                            $student_middle_name  = !empty($request->student_middle_name)?Str::upper($request->student_middle_name):'';
                            $student_last_name  = Str::upper($request->student_last_name);
                            $student_full_name = $student_first_name.' '.$student_middle_name.' '.$student_last_name;
                            $student_father_name = Str::upper($request->student_father_name);
                            $student_mother_name = Str::upper($request->student_mother_name);
                            $student_id =  $request->student_id;
                            $student_dob = $request->student_dob;
                            $student_phone = $request->student_phone;
                            $student_email = Str::lower($request->student_email);
                            $student_gender = $request->student_gender;
                            $student_religion = $request->student_religion;
                            $student_caste = $request->student_caste;
                            $student_tfw = $request->student_tfw;
                            $student_last_exam_percentage = $request->student_last_exam_percentage;
                            $student_math_percentage = $request->student_math_percentage;
                            $student_science_or_physics_percentage = $request->student_science_or_physics_percentage;
                            $institute_code = $request->institute_code;
                            //check exists
                            $check_student = SpotStudent::where('s_id', $student_id)->where('is_active', 1)->first();
                            
                            //dd($check_student);
                            DB::beginTransaction();
                            $message = 'Data updated successfully';
                            if ($check_student != null) {//Old data
                               $check = SpotStudent::where('s_phone', $student_phone)->where('s_id','!=',$student_id)->where('is_active', 1)->first();
                               //dd($check);
                               if($check){
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Phone no already exists!'
                                ], 200);
                               }else{
                                    SpotStudent::where('s_id', $student_id)
                                        ->update([
                                            's_first_name' => $student_first_name,
                                            's_middle_name' => $student_middle_name,
                                            's_last_name' => $student_last_name,
                                            's_candidate_name' => $student_full_name,
                                            's_father_name' => $student_father_name,
                                            's_mother_name' => $student_mother_name,
                                            's_dob' => $student_dob,
                                            's_phone' => $student_phone,
                                            's_email' => $student_email,
                                            's_gender' => $student_gender,
                                            's_religion' => $student_religion,
                                            's_caste' => $student_caste,
                                            's_tfw' => $student_tfw,
                                            'updated_at' => $now,
                                            's_inst_code' => $institute_code,
                                            'last_qualified_exam_percentage' => $student_last_exam_percentage,
                                            'math_percentage' => $student_math_percentage,
                                            'physics_or_science_percentage' => $student_science_or_physics_percentage,
                                        ]);
                               }
                               DB::commit();
                               auditTrail($user_id, "{$student_full_name} has been register successfully at {$now}");
                            
                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  $message,
                                    'student_id' => $student_id
                                ], 200);
                            }else{
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'No data found'
                                ], 200);
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

    //student fetcher all status
    public function getStudentOverallStatus($user_id)
    {
        $check_student = SpotStudent::where('s_id',$user_id)->where('is_active', 1)->first();
        $status = $msg = "";
        $spot_registration = false;
        if($check_student){
            $check_spot = config_schedule('SPOT_REGISTRATION');
            $check_spot_status = $check_spot['status'];
            //return $check_upgrade_status;
            if ($check_spot_status == true) {
                $spot_registration = true;
            }
            $check_alloted_status =  SpotStudentAllotment::where(['stu_id' => $check_student->s_id,'alloted_status'=>1])->first();

            $check_student_register = SpotStudentAllotment::where(['stu_id' => $check_student->s_id])->whereDate('reg_dt', date('Y-m-d'))->first();

                if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_student->is_counselling_fees == 1 && $check_alloted_status == null && $check_student_register == null){
                    $status = "COUNSELLING_FEES_AND_REGISTRATION_PAID_PERCENTAGE_UPDATED";
                    $msg = "Counselling and registration fees paid";
                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0  && $check_student->is_counselling_fees == 0){
                    $status = "COUNSELLING_AND_REGISTRATION_FEES_NOT_PAID_PERCENTAGE_UPDATED";
                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0  && $check_student->is_counselling_fees == 0){
                    $status = "COUNSELLING_AND_REGISTRATION_FEES_NOT_PAID_PERCENTAGE_NOT_UPDATED";
                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_counselling_fees == 0){
                    $status = "COUNSELLING_FEES_NOT_PAID_PERCENTAGE_NOT_UPDATED";
                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0 && $check_student->is_counselling_fees == 1){
                    $status = "REGISTRATION_FEES_NOT_PAID_COUNSELLING_FEES_PAID_PERCENTAGE_NOT_UPDATED";
                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_counselling_fees == 1){
                    $status = "COUNSELLING_FEES_PAID_PERCENTAGE_NOT_UPDATED";
                }else if(is_null($check_student->last_qualified_exam_percentage) && is_null($check_student->math_percentage) && is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1){
                    $status = "REGISTRATION_FEES_PAID_PERCENTAGE_NOT_UPDATED";
                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_student->is_counselling_fees == 0){
                    $status = "REGISTRATION_FEES_PAID_PERCENTAGE_UPDATED_COUNSELLING_FEES_NOT_PAID";
                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 0 && $check_student->is_counselling_fees == 1){
                    $status = "REGISTRATION_FEES_NOT_PAID_COUNSELLING_FEES_PAID_PERCENTAGE_UPDATED";
                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_student->is_counselling_fees == 1 && $check_alloted_status != null){
                    $status = "ALREADY_ALLOTED";
                    $msg = "You are already alloted";
                }else if(!is_null($check_student->last_qualified_exam_percentage) && !is_null($check_student->math_percentage) && !is_null($check_student->physics_or_science_percentage) && $check_student->is_payment == 1 && $check_student->is_counselling_fees == 1 && $check_alloted_status == null && $check_student_register!= null){
                    $status = "REGISTERED_FOR_ALLOTMENT";
                    $msg = "You are registered for today's allotement";
                }
            $data = [
                'student_id' => isset($check_student->s_id)?$check_student->s_id:'',
                'student_uid' => isset($check_student->s_uuid)?$check_student->s_uuid:'',
                'student_inst_code' => isset($check_student->s_inst_code)?$check_student->s_inst_code:'',
                'student_first_name' => isset($check_student->s_first_name)?$check_student->s_first_name:'',
                'student_middle_name' => isset($check_student->s_middle_name)?$check_student->s_middle_name:'',
                'student_last_name' => isset($check_student->s_last_name)?$check_student->s_last_name:'',
                'student_father_name' => isset($check_student->s_father_name)?$check_student->s_father_name:'',
                'student_mother_name' => isset($check_student->s_mother_name)?$check_student->s_mother_name:'',
                'student_dob' => isset($check_student->s_dob)?$check_student->s_dob:'',
                'student_phone' => isset($check_student->s_phone)?$check_student->s_phone:'',
                'student_email' => isset($check_student->s_email)?$check_student->s_email:'',
                'student_gender' => isset($check_student->s_gender)?$check_student->s_gender:'',
                'student_religion' => isset($check_student->s_religion)?$check_student->s_religion:'',
                'student_caste' => isset($check_student->s_caste)?$check_student->s_caste:'',
                'student_tfw' => isset($check_student->s_tfw)?$check_student->s_tfw:'',
                'student_pwd' => isset($check_student->s_pwd)?$check_student->s_pwd:'',
                'student_overall_status' => $status,
                'student_last_exam_percentage' => isset($check_student->last_qualified_exam_percentage)?$check_student->last_qualified_exam_percentage:'',
                'student_math_percentage' => isset($check_student->math_percentage)?$check_student->math_percentage:'',
                'student_science_or_physics_percentage' => isset($check_student->physics_or_science_percentage)?$check_student->physics_or_science_percentage:'',
                'msg' => $msg,
                'spot_registration_status' => $spot_registration
            ];
            return response()->json([
                'error'     =>  false,
                'message'   =>  'Student is available',
                'data' => $data
            ], 200);
        }else{
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Student is not avilable!'
            ], 200);
        }
    }

    //Spot Institute List
    public function allInstList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $stream    =   $request->stream;
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_role = $request->role_id;
                if (!empty($user_role)) {
                    $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', $user_role)->pluck('rp_url_id');
                } else {
                    $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');
                }

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('institute-stream-wise', $url_data)) { //check url has permission or not
                        $inst_res = null;
						$res = null;
                        //dd($user_role);

                        if($user_role == 2) {   //if student
                            $inst_list = DB::table('jexpo_spot_seat_master as sm')
                                        ->join('institute_master as im', 'im.i_code', '=', 'sm.sm_inst_code')
                                        ->select([
                                            'im.i_id as institute_id',
                                            'sm.sm_inst_code as institute_code',
                                            'im.i_name as institute_name',
                                            'im.i_type as institute_type',
                                        ])
                                        ->orderBy('i_name', 'ASC')
                                        ->distinct()
                                        ->where('im.is_active', 1)
                                        ->where('im.i_type','!=', 'PVT')
                                        ->whereRaw('(sqogen + sqosc + sqost + sqpwd + tfw) > 0');

                            $inst_res = $inst_list->get();
                            $res = $inst_res;
                        }   

                        if (sizeof($inst_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Institute found',
                                'count'     =>   sizeof($inst_res),
                                'instituteList'   =>  $res
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

    //Spot Stream List Inst wise
    public function streamListinstListWise(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_role = $request->role_id;

                if (!empty($user_role)) {
                    $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', $user_role)->pluck('rp_url_id');
                } else {
                    $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');
                }

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('institute-wise-stream', $url_data)) { //check url has permission or not

                        $validated = Validator::make($request->all(), [
                            'inst_code' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $inst_code    =   $request->inst_code;
						$res = null;
                        
                        if($user_role == 2) {   //if student
                            $trade_res = DB::table('jexpo_spot_seat_master as sm')
                                    ->join('trade_master as tm', 'tm.t_code', '=', 'sm.sm_trade_code')
                                    ->select([
                                        'tm.t_id as trade_id',
                                        'sm.sm_trade_code as trade_code',
                                        'tm.t_name as trade_name',
                                    ])
                                    ->where('sm.sm_inst_code', $inst_code)
                                    ->whereRaw('(sqogen + sqosc + sqost + sqpwd + tfw) > 0')
                                    ->orderBy('tm.t_name', 'asc')
                                    ->get();

                            $res =  $trade_res;       
                        }
                        
                        if (sizeof($trade_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Stream found',
                                'count'     =>   sizeof($trade_res),
                                'tradeList'   =>  $res
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

    //Spot Student choice entry
    public function spotchoiceEntry(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-entry', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'trade_code' => ['required'],
                            'inst_code' => ['required'],
                            'choice_id' => ['nullable'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $trade_code = $request->trade_code;
                            $inst_code = $request->inst_code;
                            $choice_id = $request->choice_id;
                            $old_choice_pref_no = 1;

                            $tradeData = Trade::where(['t_code' => $trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $inst_code, 'is_active' => 1])->first();

                            $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                            $checkExists = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_trade_code', $trade_code)->where('ch_inst_code', $inst_code)->first();

                            if ($checkExists) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists with the same choice, please try other!"
                                ], 200);
                            } else {
                                $choice_cnt = SpotStudentChoice::where('ch_stu_id', $user_id)->count();
                                //return $choice_cnt;
                                $choice_pref_no = $choice_cnt + 1;
                                $student_name = $user_data->s_candidate_name;

                                if (!is_null($choice_id)) {
                                    $old_choice_pref = SpotStudentChoice::where('ch_id', $choice_id)->first();

                                    if ($old_choice_pref) {
                                        $old_choice_pref_no = $old_choice_pref->ch_pref_no;
                                    }
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;

                                    $old_choice_pref->update([
                                        'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice updated successfully";
                                } else {
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;
                                    SpotStudentChoice::create([
                                        'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                        'ch_fillup_time' => now()
                                    ]);

                                    $message = "{$student_name} selected choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice created successfully";
                                }

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  $resp_msg
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Spot Student choice list
    public function spotchoiceList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id', 'is_lock_manual', 'is_lock_auto')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-list', $url_data)) { //check url has permission or not
                        $choice_res = null;
                        $choice_list = SpotStudentChoice::where('ch_stu_id',  $user_id)->with('student')->orderBy('ch_pref_no', 'ASC')->get();

                        if (sizeof($choice_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student choice found',
                                'count'     =>   sizeof($choice_list),
                                'choiceList'   =>  SpotStudentChoiceResource::collection($choice_list)
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

    //Spot Student choice remove
    public function spotchoiceRemove(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-remove', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;

                            $oldData = SpotStudentChoice::where('ch_id', $choice_id)->first();
                            $old_pref_no = $oldData->ch_pref_no;
                            $old_trade_code = $oldData->ch_trade_code;
                            $old_inst_code = $oldData->ch_inst_code;

                            $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                            $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                            $del = $oldData->delete();
                            //dd($del);
                            if ($del == 1) {
                                //dd($old_pref_no);
                                $list = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', '>', $old_pref_no)->get();
                                //return $list;
                                if (sizeof($list) > 0) {
                                    foreach ($list as $key => $val) {
                                        $val->update([
                                            'ch_pref_no' => (int)$val->ch_pref_no - 1,
                                        ]);
                                    }
                                }


                                $student_name = $user_data->s_candidate_name;

                                $message = "{$student_name} removed choice #{$old_pref_no} having [{$old_inst_code}] - {$inst_name} and [{$old_trade_code}]  -  {$trade_name}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Choice removed successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Student choice does not exist"
                                ], 200);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Spot Student up/down choice
    public function spotchoiceUpDown(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-up-down', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                            'type' => ['required'],
                            'old_preference_no' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;
                            $type = $request->type;
                            $old_choice_pref_no = (int)$request->old_preference_no;
                            //$new_choice_pref_no = "";
                            $student_name = $user_data->s_candidate_name;

                            if ($type == 'up') {
                                $new_choice_pref_no = (int)($old_choice_pref_no - 1);
                                $existingData = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();

                                

                                $toData = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                $old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;


                                $to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();


                                $totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                                $to_trade_name = !is_null($totradeData->t_name) ? $totradeData->t_name : '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';


                                if ($existingData) {
                                    SpotStudentChoice::where('ch_id', $existingData->ch_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$old_choice_pref_no} to #{$new_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                SpotStudentChoice::where('ch_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                // $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                // auditTrail($user_id, $message);
                                // studentActivite($user_id, $message);
                            } else {
                                $new_choice_pref_no = (int)($old_choice_pref_no + 1);
                                $existingData = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();
                                //dd($existingData);
                                $toData = SpotStudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                $old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;

                                $to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                $tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                                $totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = !is_null($tradeData->t_name) ? $tradeData->t_name : '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                                $to_trade_name = !is_null($totradeData->t_name) ? $totradeData->t_name : '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';

                                if ($existingData) {
                                    SpotStudentChoice::where('ch_id', $existingData->ch_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                SpotStudentChoice::where('ch_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                /* $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message); */
                            }

                            DB::commit();
                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Choice updated successfully"
                            ], 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Spot Student bulk final submit
    public function spotchoiceLockFinalSubmit(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('jexpo_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('jexpo_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-lock-final-submit', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            SpotStudent::where('s_id', $user_id)->update([
                                'is_lock_manual' => 1,
                                'is_choice_fill_up' => 1,
                                'manual_lock_at' => now()
                            ]);
                            $student_name = $user_data->s_candidate_name;
                            $redirect = $this->checkRedirect($user_id);

                            $message = "{$student_name} submitted and locked choices";

                            auditTrail($user_id, $message);
                            studentActivite($user_id, $message);

                            DB::commit();
                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  "Choices are successfully locked and submitted",
                                'redirect'  =>  $redirect
                            ], 200);
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
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

    //Spot Student pdf
    public function SpotRegistrationPdf(Request $request){
        $student_uuid = $request->student_id;
        if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $student_uuid) == 1) {
            echo 'Comming Soon'; die();
            $spotStudentDetails = SpotStudent::where(['is_active'=>1,'is_payment'=>1,'is_counselling_fees'=>1,'s_uuid'=>$student_uuid])->with('institute:i_code,i_name')->first();
            //return $spotStudentDetails->institute->i_name;
            if($spotStudentDetails){
                $student_name = $spotStudentDetails->s_candidate_name;
                $student_father_name = $spotStudentDetails->s_father_name;
                $student_mother_name = $spotStudentDetails->s_mother_name;
                $student_dob = $spotStudentDetails->s_dob;
                $student_phone = $spotStudentDetails->s_phone;
                $student_email = $spotStudentDetails->s_email;
                $student_gender = $spotStudentDetails->s_gender;
                $student_religion = $spotStudentDetails->s_religion;
                $student_caste = $spotStudentDetails->s_caste;
                $student_tfw = $spotStudentDetails->s_tfw;
                $student_pwd = $spotStudentDetails->s_pwd;
                $student_10th_percentage = $spotStudentDetails->last_qualified_exam_percentage;
                $student_math_percentage = $spotStudentDetails->math_percentage;
                $student_science_percentage = $spotStudentDetails->physics_or_science_percentage;
                $student_chosen_institute = $spotStudentDetails->institute->i_name;

                $finalList = [
                    'institute_name' =>  $student_chosen_institute,
                    'candidate_gender' => $student_gender,
                    'candidate_name' => Str::upper($student_name),
                    'candidate_guardian_name' => !is_null($student_father_name) ? Str::upper($student_father_name) : "N/A",
                    'candidate_mother_name' => !is_null($student_mother_name) ? Str::upper($student_mother_name) : "N/A",
                    'candidate_caste' => $student_caste,
                    'candidate_physically_challenged' => ($student_pwd == 0) ? "No" : "Yes",
                    'candidate_dob' => Carbon::parse($student_dob)->format('d/m/Y'),
                    'candidate_phone' => $student_phone,
                    'candidate_email' => $student_email,
                    'candidate_religion' => $student_religion,
                    'candidate_tfw' => ($student_tfw == 0) ? "No" : "Yes",
                    'candidate_10th_percentage' => $student_10th_percentage,
                    'candidate_math_percentage' => $student_math_percentage,
                    'candidate_science_percentage' => $student_science_percentage,
                ];

                //return $finalList;

                $message = "{$student_name} downloaded the final allotment letter";

                
                $pdf = Pdf::loadView('exports.spotregistration', [
                    'data' => $finalList,
                ]);
                return $pdf->setPaper('a4', 'portrait')
                    ->setOption(['defaultFont' => 'sans-serif'])
                    ->stream('spot_allotment.pdf');

            }else{
                $reponse = array(
                    'error'     =>  false,
                    'message'   =>  'No data found!'
                );
                return response(json_encode($reponse), 200);
            }
        }else{
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'Invalid Request!'
            );
            return response(json_encode($reponse), 200);
        }
    }


}
