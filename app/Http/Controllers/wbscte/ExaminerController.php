<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\wbscte\Institute;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\ExternelExaminerMap;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\wbscte\InstituteResource;
use App\Models\wbscte\OtherDiplomaExaminnerInstitute;
use App\Http\Resources\wbscte\ExaminerInternalResource;
use App\Http\Resources\wbscte\ExternelExaminerResource;
use App\Http\Resources\wbscte\InternalExaminerShowResource;
use App\Http\Resources\wbscte\SpecialExaminerResource;

class ExaminerController extends Controller
{
    public function examinerEntry(Request $request)
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

                    if (in_array('examiner-entry', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'examiner_name' => ['required'],
                            // 'semester' => ['required'],
                            'u_phone' => 'required|digits:10|unique:wbscte_other_diploma_users_master,u_phone',
                            'examiner_email' => ['required', 'email'],
                            'bank_account_holder_name' => ['required'],
                            'bank_account_no' => ['required'],
                            'bank_ifsc' => ['required'],
                            'bank_branch_name' => ['required'],
                            'is_direct' => ['required'],
                        ], [
                            'u_phone.unique' => 'Phone already taken'
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {
                            $is_direct = $request->is_direct;
                            $inst_id = $request->inst_id;
                            // $semester = $request->semester;
                            $examiner_name = $request->examiner_name;
                            $examiner_phone = $request->u_phone;
                            $examiner_email = $request->examiner_email;
                            $bank_account_name = $request->bank_account_holder_name;
                            $bank_account_no = $request->bank_account_no;
                            $bank_branch = $request->bank_branch_name;

                            $bank_ifsc = $request->bank_ifsc;
                            $user_role_id = 3;

                            $new_user = User::create([
                                'u_ref' => generateRandomCode(),
                                'u_fullname' => $examiner_name,
                                'u_phone' => $examiner_phone,
                                'u_email' => $examiner_email,
                                'bank_account_holder_name' => $bank_account_name,
                                'bank_account_no' => $bank_account_no,
                                'bank_ifsc' => $bank_ifsc,
                                'bank_branch_name' => $bank_branch,
                                'u_role_id' => $user_role_id,
                                'is_active' => 1,
                                'is_direct' => $is_direct,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                            // dd($new_user);

                            if (!$is_direct) {
                                $ins_detail = Institute::find($request->inst_id);

                                OtherDiplomaExaminnerInstitute::create([
                                    'examiner_user_id' => $new_user->u_id,
                                    'examiner_inst_id' => $inst_id,
                                    'examiner_inst_code' => $ins_detail->institute_code,
                                    // 'examiner_part_sem' => $semester,
                                    'examiner_bank_account_holder_name' => $bank_account_name,
                                    'examiner_bank_account_no' => $bank_account_no,
                                    'examiner_bank_ifsc' => $bank_ifsc,
                                    'examiner_bank_branch_name' => $bank_branch,
                                    'examiner_name' => $examiner_name,
                                    'examiner_phone' => $examiner_phone,
                                    'examiner_email' => $examiner_email,
                                    // 'examiner_paper_entry' => $request->paper_entry_type,
                                    // 'examiner_paper_type' => $request->paper_type,
                                    'is_active' => 1,
                                ]);
                            }

                            if ($is_direct) {
                                auditTrail($user_id, "{$examiner_name} has added as an user");
                            } else {
                                $inst_name = $ins_detail->institute_name;
                                auditTrail($user_id, "{$examiner_name} has added as an internel examiner of {$inst_name}");
                            }

                            DB::commit();

                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Examiner entry successfull"
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

    public function internelExaminerTag(Request $request)
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

                    if (in_array('internel-examiner-tag', $url_data)) { //check url has permission or not

                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'examiner_id' => ['required'],
                            'semester' => ['required'],
                            'course_id' => 'required',
                            'paper_id' => 'required',
                            'paper_entry_type' => 'required',
                            'paper_type' => 'required',
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {
                            $examinerTags = OtherDiplomaExaminnerInstitute::query();
                            $examinerData = User::find($request->examiner_id);
                            $check = $examinerTags->clone()->where(['examiner_inst_id' => $request->inst_id, 'examiner_user_id' => $request->examiner_id, 'examiner_course_id' => $request->course_id, 'examiner_paper_id' => $request->paper_id, 'examiner_part_sem' => $request->semester, 'map_paper_entry_type' => $request->paper_entry_type, 'map_paper_type' => $request->paper_type,'assign_status' => $request->assign_status])->first();
                            if ($check) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists same!"
                                ], 200);
                            
                            }else {
                                $a = $examinerTags->clone()->where(['is_active' => 1, 'examiner_inst_id' => $request->inst_id, 'examiner_user_id' => $request->examiner_id])->whereNull('examiner_paper_id')->first();
                                if ($a) {
                                    $examiner_id = $a->examiner_id;
                                    $examiner_update = $examinerTags->clone()->where('examiner_id',$examiner_id)->update([
                                        'examiner_course_id'    =>  $request->course_id,
                                        'examiner_paper_id'    =>  $request->paper_id,
                                        'is_active' => '1',
                                        'assign_status' => '1',
                                        'examiner_part_sem'=> $request->semester,
                                        'map_paper_entry_type' => $request->paper_entry_type,
                                        'map_paper_type' => $request->paper_type,
                                    ]);

                                } else {

                                    $ins_detail = Institute::find($request->inst_id);

                                    $examinerData =     OtherDiplomaExaminnerInstitute::create([
                                        'examiner_course_id'           =>  $request->course_id,
                                        'examiner_paper_id'    =>  $request->paper_id,
                                        'examiner_user_id'     => $request->examiner_id,
                                        'examiner_inst_id' => $request->inst_id,
                                        'examiner_inst_code' => $ins_detail->institute_code,
                                        'examiner_part_sem' => $request->semester,
                                        'examiner_bank_account_holder_name' => $examinerData->bank_account_holder_name,
                                        'examiner_bank_account_no' => $examinerData->bank_account_no,
                                        'examiner_bank_ifsc' => $examinerData->bank_ifsc,
                                        'examiner_bank_branch_name' => $examinerData->bank_branch_name,
                                        'examiner_name' => $examinerData->u_fullname,
                                        'examiner_phone' => $examinerData->u_phone,
                                        'examiner_email' => $examinerData->u_email,
                                        'is_active' => '1',
                                        'assign_status' => '1',
                                        'map_paper_entry_type' => $request->paper_entry_type,
                                        'map_paper_type' => $request->paper_type,
                                    ]);
                                }
                            }

                            DB::commit();
                            return response()->json([
                                'error'             =>  false,
                                
                                'message'              =>  "Examiner tagged successfull"
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

    public function internalExaminerTagList(Request $request,$session_yr = null,$semester = null)
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

                    if (in_array('internal-examiner-tag-list', $url_data)) { 
                        //check url has permission or not
                        if($semester == null || $session_yr == null){
                            $examinerTagsList = OtherDiplomaExaminnerInstitute::query();
                            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->distinct('examiner_user_id')->orderBy('examiner_user_id', 'DESC')->get();
                            } else if ($user_data->u_role_id == '2') { //Institute Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('examiner_inst_id', $user_data->u_inst_id)->distinct('examiner_user_id')->orderBy('examiner_user_id', 'DESC')->get();
                            }
                        }else{
                            $examinerTagsList = OtherDiplomaExaminnerInstitute::query();
                            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->whereHas('course', function ($query) use ($session_yr) {
                                    $query->where('course_affiliation_year', $session_yr);
                                })->where('examiner_part_sem',$semester)->distinct('examiner_user_id')->orderBy('examiner_user_id', 'DESC')->get();
                            } else if ($user_data->u_role_id == '2') { //Institute Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('examiner_inst_id', $user_data->u_inst_id)->whereHas('course', function ($query) use ($session_yr) {
                                    $query->where('course_affiliation_year', $session_yr);
                                })->where('examiner_part_sem',$semester)->distinct('examiner_user_id')->orderBy('examiner_user_id', 'DESC')->get();
                            }

                        }
                        
                        //return $examiner_tag_list;
                        if (sizeof($examiner_tag_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner List found',
                                'count'         =>   sizeof($examiner_tag_list),
                                'lists'   =>  ExaminerInternalResource::collection($examiner_tag_list)
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

    public function internalExaminerTagShow(Request $request, $id)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            // dd($request->header('token'));
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('internal-examiner-tag-show', $url_data)) { //check url has permission or not

                        if (is_null($id)) {
                            throw ValidationException::withMessages(['id' => 'id is required as path param']);
                        }
                        $examinerTagDetails = OtherDiplomaExaminnerInstitute::where('examiner_id', $id)->with(['user','course'])->first();
                        if ($examinerTagDetails) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner Details found',
                                'details'   =>  new InternalExaminerShowResource($examinerTagDetails),
                                'courses' => $examinerTagDetails->courses
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

    public function taggableInstitutionList(Request $request)
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

                    if (in_array('taggable-institution-list', $url_data)) { //check url has permission or not

                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'paper_type' => ['required'],

                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            $inst_id = $request->inst_id;
                            $paper_type = $request->paper_type;
                            if(!empty($paper_type) && $paper_type == 2){
                                $inst_list = Institute::where('is_active', 1)
                                ->orderby('inst_sl_pk')->get();
                            }else {
                                // $inst_list = Institute::where('is_active', 1)
                                // ->whereNotIn('inst_sl_pk', [$inst_id])        //changes not institute
                                // ->get();
                                $inst_list = Institute::where('is_active', 1)
                                ->get();
                                
                            }
                            //  dd($inst_list) ;

                            if (sizeof($inst_list) > 0) {
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Examiner List found',
                                    'count'         =>   sizeof($inst_list),
                                    'lists'   =>  InstituteResource::collection($inst_list)
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

    public function externelExaminerTag(Request $request)
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

                    if (in_array('externel-examiner-tag', $url_data)) { //check url has permission or not

                        $validated = Validator::make($request->all(), [
                            'semester' => ['required'],
                            'session_year' => ['required'],
                            'paper_type' => ['required'],
                            'paper_entry_type' => ['required'],
                            'course_id' => ['required'],
                            'paper_id' => ['required'],
                            'is_direct' => ['required'],
                            'source_inst_id' => ['nullable', Rule::requiredIf($request->is_direct == false)],
                            'source_inst_name' => ['nullable', Rule::requiredIf($request->is_direct == false)],
                            'examiner_id' => ['required'],
                            'examiner_name' => ['required'],
                            'taggable_inst_ids' => ['required', 'array'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            try {
                                $semester = $request->semester;
                                $session = $request->session_year;
                                $paper_type = $request->paper_type;
                                $paper_entry_type = $request->paper_entry_type;
                                $course_id = $request->course_id;
                                $paper_id = $request->paper_id;
                                $examiner_id = $request->examiner_id;
                                $examiner_name = $request->examiner_name;
                                $source_inst_id = $request->source_inst_id;
                                $source_inst_name = $request->source_inst_name;
                                $taggable_inst_ids = $request->taggable_inst_ids;
                                $is_direct = $request->is_direct;

                                DB::beginTransaction();

                                $delval = ExternelExaminerMap::where([
                                    'map_sem' => $semester,
                                    'map_affiliation_year' => $session,
                                    'map_paper_type' => $paper_type,
                                    'map_paper_entry_type' => $paper_entry_type,
                                    'map_course_id' => $course_id,
                                    'map_paper_id' => $paper_id,
                                    'map_examiner_id' => $examiner_id,
                                    'map_examiner_name' => $examiner_name,
                                    'is_active' => 1
                                ])->delete();

                                if ($delval > 0) {
                                    $txt = 'Updated';
                                } else {
                                    $txt = 'added';
                                }
                                foreach ($taggable_inst_ids as $inst_tag_id) {
                                    ExternelExaminerMap::create([
                                        'map_sem' => $semester,
                                        'map_affiliation_year' => $session,
                                        'map_paper_type' => $paper_type,
                                        'map_paper_entry_type' => $paper_entry_type,
                                        'map_course_id' => $course_id,
                                        'map_paper_id' => $paper_id,
                                        'map_examiner_id' => $examiner_id,
                                        'map_examiner_name' => $examiner_name,
                                        'is_active' => 1,
                                        'map_source_inst_id' => $source_inst_id ?? null,
                                        'map_assign_inst_id' => $inst_tag_id,
                                        'created_on' => now(),
                                        'updated_on' => now(),
                                        'created_by' => $user_id,
                                        'updated_by' => $user_id,
                                    ]);
                                    // exit();
                                    $ext_inst_name = Institute::find($inst_tag_id, 'institute_name')->institute_name;

                                    if ($is_direct) {
                                        auditTrail($user_id, "{$examiner_name} has {$txt} as an externel examiner of {$ext_inst_name}");
                                    } else {
                                        auditTrail($user_id, "{$examiner_name} has {$txt} as an externel examiner of {$ext_inst_name} who is an internel examiner of {$source_inst_name}");
                                    }
                                }

                                DB::commit();

                                $ext_list = ExternelExaminerMap::select('map_id', 'map_examiner_name', 'map_source_inst_id', 'map_assign_inst_id', 'map_sem', 'map_affiliation_year', 'map_paper_type', 'map_paper_entry_type', 'map_course_id', 'map_paper_id', 'map_examiner_id', 'is_active')
                                    ->where([
                                        'map_sem' => $semester,
                                        'map_affiliation_year' => $session,
                                        'map_paper_type' => $paper_type,
                                        'map_paper_entry_type' => $paper_entry_type,
                                        'map_course_id' => $course_id,
                                        'map_paper_id' => $paper_id,
                                        'map_examiner_id' => $examiner_id,
                                        'is_active' => 1
                                    ])
                                    ->with('internelInstituteName:inst_sl_pk,institute_name', 'ExternerInstituteName:inst_sl_pk,institute_name')
                                    ->get();
                                    // return ($ext_list);



                                if (sizeof($ext_list) > 0) {
                                    $reponse = array(
                                        'error'         =>  false,
                                        'message'       =>  "External Examiner Tag {$txt}",
                                        'count'         =>   sizeof($ext_list),
                                        'lists'   =>  ExternelExaminerResource::collection($ext_list)
                                    );
                                    return response(json_encode($reponse), 200);
                                } else {
                                    $reponse = array(
                                        'error'     =>  true,
                                        'message'   =>  'No Data available'
                                    );
                                    return response(json_encode($reponse), 200);
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

    public function externelExaminerList(Request $request,$session_yr = null,$semester = null)
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

                    if (in_array('externel-examiner-list', $url_data)) { //check url has permission or not
                        if($semester == null || $session_yr == null){

                            $ext_list = ExternelExaminerMap::select('map_id', 'map_examiner_name', 'map_source_inst_id', 'map_assign_inst_id', 'map_course_id', 'map_paper_id','map_examiner_id','map_paper_type','map_paper_entry_type','map_affiliation_year')
                                ->with('internelInstituteName:inst_sl_pk,institute_name', 'ExternerInstituteName:inst_sl_pk,institute_name')
                                ->with(['course:course_id_pk,course_name', 'paper:paper_id_pk,paper_name','user:u_id,u_phone'])
                                ->get();
                        }else{
                            $ext_list = ExternelExaminerMap::select('map_id', 'map_examiner_name', 'map_source_inst_id', 'map_assign_inst_id', 'map_course_id', 'map_paper_id','map_examiner_id','map_paper_type','map_paper_entry_type','map_affiliation_year')
                                ->with('internelInstituteName:inst_sl_pk,institute_name', 'ExternerInstituteName:inst_sl_pk,institute_name')
                                ->with(['course:course_id_pk,course_name', 'paper:paper_id_pk,paper_name','user:u_id,u_phone'])
                                ->where('map_affiliation_year',$session_yr)
                                ->where('map_sem',$semester)
                                ->get();

                        }
                        // return $ext_list;

                        if (sizeof($ext_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Externel Examiner List found',
                                'count'         =>   sizeof($ext_list),
                                'lists'   =>  ExternelExaminerResource::collection($ext_list)
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

    public function updateInternalExaminer(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                // dd($user_id);

                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('internal-examiner-update', $url_data)) { //check url has permission or not
                        $examinerTags = OtherDiplomaExaminnerInstitute::query();
                        $examiner_id = $request->examiner_id;
                        // dd($examiner_id);
                        $examiner_data = $examinerTags->clone()->find($examiner_id);

                        $new_user = User::find($examiner_data->examiner_user_id);
                        // dd($new_user) ;
                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'examiner_id' => ['required'],
                            'course_id' => ['required'],
                            'paper_id' => ['required'],
                            'examiner_name' => ['required'],
                            // 'semester' => ['required'],
                            // 'u_phone' => ['required', Rule::unique('wbscte_other_diploma_users_master', 'u_phone')->ignore($new_user->u_id, 'u_id')],
                            // 'examiner_email' => ['required', 'email'],
                            // 'bank_account_holder_name' => ['required'],
                            // 'bank_account_no' => ['required'],
                            // 'bank_ifsc' => ['required'],
                            // 'bank_branch_name' => ['required'],
                            'is_confirmed' => ['required'],
                            'paper_entry_type' => 'required',
                            'paper_type' => 'required',

                        ]);
                        //dd($request->all());
                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            try {
                                DB::beginTransaction();

                                $inst_id = $request->inst_id;
                                $course_id = $request->course_id;
                                $paper_id = $request->paper_id;
                                $semester = $request->semester;
                                $examiner_name = $request->examiner_name;
                                // $examiner_phone = $request->u_phone;
                                // $examiner_email = $request->examiner_email;
                                // $bank_account_name = $request->bank_account_holder_name;
                                // $bank_account_no = $request->bank_account_no;
                                // $bank_branch = $request->bank_branch_name;
                                // $bank_ifsc = $request->bank_ifsc;
                                $is_confirmed = $request->is_confirmed;


                                $ins_detail = Institute::find($request->inst_id);

                                $new_check = $examinerTags->clone()
                                    ->where([
                                        'examiner_inst_id' => $request->inst_id,
                                        'examiner_user_id' => $examiner_data->examiner_user_id,
                                        'examiner_course_id' => $request->course_id,
                                        'examiner_part_sem' => $request->semester,
                                        'examiner_id' => $examiner_id,
                                        // 'examiner_paper_entry' => $request->paper_entry_type,
                                        // 'examiner_paper_type' => $request->paper_type
                                        'map_paper_entry_type' => $request->paper_entry_type,
                                        'map_paper_type' => $request->paper_type,

                                    ])
                                    ->first();

                                 //dd($new_check);

                                $new_user->update([
                                    'u_fullname' => $examiner_name,
                                    'updated_at' => $now,

                                ]);

                                if ($new_check) {
                                    $new_check->update([
                                        // 'examiner_bank_account_holder_name' =>  $bank_account_name,
                                        // 'examiner_bank_account_no' =>  $bank_account_no,
                                        // 'examiner_bank_ifsc' =>  $bank_ifsc,
                                        // 'examiner_bank_branch_name' =>  $bank_branch,
                                        'examiner_name' =>  $examiner_name,
                                        // 'examiner_phone' =>  $examiner_phone,
                                        // 'examiner_email' =>  $examiner_email,
                                        // 'examiner_paper_entry' => $request->paper_entry_type,
                                        // 'examiner_paper_type' => $request->paper_type,
                                        'map_paper_entry_type' => $request->paper_entry_type,
                                        'map_paper_type' => $request->paper_type,
                                    ]);
                                    $is_old_found = false;

                                    $old_check = $examinerTags->clone()
                                        ->where([
                                            'examiner_inst_id' => $request->inst_id,
                                            'examiner_course_id' => $request->course_id,
                                            'examiner_paper_id' => $request->paper_id,
                                            'examiner_part_sem' => $request->semester,
                                            // 'examiner_paper_entry' => $request->paper_entry_type,
                                            // 'examiner_paper_type' => $request->paper_type
                                            'map_paper_entry_type' => $request->paper_entry_type,
                                            'map_paper_type' => $request->paper_type,
                                        ])
                                        ->whereNot('examiner_id', $examiner_id)
                                        ->first();

                                    $old_user = $old_check;

                                    if ($is_confirmed == false) {

                                        /*if (!is_null($old_check)) {
                                            return response()->json([
                                                'error' => true,
                                                'is_old_found' => $is_old_found,
                                                'message' => "Examiner already exists for this paper"
                                            ], 200);
                                        } else {*/
                                            $new_check->update([
                                                'examiner_inst_id' => $inst_id,
                                                'examiner_inst_code' =>  $ins_detail->institute_code,
                                                'examiner_part_sem' =>  $semester,
                                                'examiner_course_id' => $course_id,
                                                'examiner_paper_id' => $paper_id,
                                                // 'examiner_paper_entry' => $request->paper_entry_type,
                                                // 'examiner_paper_type' => $request->paper_type,
                                                'map_paper_entry_type' => $request->paper_entry_type,
                                                'map_paper_type' => $request->paper_type,

                                            ]);
                                            // dd($new_check);
                                        //}
                                    } else {
                                        if (!is_null($old_check)) {
                                            $old_user->delete();
                                            $new_check->update([
                                                'examiner_inst_id' => $inst_id,
                                                'examiner_inst_code' =>  $ins_detail->institute_code,
                                                'examiner_part_sem' =>  $semester,
                                                'examiner_course_id' => $course_id,
                                                'examiner_paper_id' => $paper_id,
                                                // 'examiner_paper_entry' => $request->paper_entry_type,
                                                // 'examiner_paper_type' => $request->paper_type,
                                                'map_paper_entry_type' => $request->paper_entry_type,
                                                'map_paper_type' => $request->paper_type,
                                            ]);
                                        }
                                    }
                                } else {
                                    $is_old_found = false;

                                    $old_check = $examinerTags->clone()
                                        ->where([
                                            'examiner_inst_id' => $request->inst_id,
                                            'examiner_course_id' => $request->course_id,
                                            'examiner_paper_id' => $request->paper_id,
                                            'examiner_part_sem' => $request->semester,
                                            // 'examiner_paper_entry' => $request->paper_entry_type,
                                            // 'examiner_paper_type' => $request->paper_type
                                            'map_paper_entry_type' => $request->paper_entry_type,
                                            'map_paper_type' => $request->paper_type,
                                        ])
                                        ->whereNot('examiner_id', $examiner_id)
                                        ->first();
                                    $old_user = $old_check;
                                    //dd($old_user);

                                    /*if (!is_null($old_check) && $is_confirmed == false) {
                                        $is_old_found = true;

                                        return response()->json([
                                            'error' => true,
                                            'is_old_found' => $is_old_found,
                                            'message' => "Examiner already exists for this paper"
                                        ], 200);
                                    }*/

                                    if ($is_confirmed == true) {
                                        $res = $old_user->delete();

                                        if ($res) {
                                            OtherDiplomaExaminnerInstitute::create([
                                                'examiner_inst_id' => $inst_id,
                                                'examiner_inst_code' =>  $ins_detail->institute_code,
                                                'examiner_part_sem' =>  $semester,
                                                'examiner_course_id' => $course_id,
                                                'examiner_paper_id' => $paper_id,
                                                // 'examiner_bank_account_holder_name' =>  $bank_account_name,
                                                // 'examiner_bank_account_no' =>  $bank_account_no,
                                                // 'examiner_bank_ifsc' =>  $bank_ifsc,
                                                // 'examiner_bank_branch_name' =>  $bank_branch,
                                                'examiner_name' =>  $examiner_name,
                                                // 'examiner_phone' =>  $examiner_phone,
                                                // 'examiner_email' =>  $examiner_email,
                                                'examiner_user_id' =>  $new_user->u_id,
                                                'is_active' =>  '1',
                                                // 'examiner_paper_entry' => $request->paper_entry_type,
                                                // 'examiner_paper_type' => $request->paper_type,
                                                'map_paper_entry_type' => $request->paper_entry_type,
                                                'map_paper_type' => $request->paper_type,
                                            ]);
                                        }
                                    } else {
                                        //dd('hi');
                                        $res = OtherDiplomaExaminnerInstitute::where('examiner_id', $examiner_id)->delete();
                                        if ($res) {
                                            OtherDiplomaExaminnerInstitute::create([
                                                'examiner_inst_id' => $inst_id,
                                                'examiner_inst_code' =>  $ins_detail->institute_code,
                                                'examiner_part_sem' =>  $semester,
                                                'examiner_course_id' => $course_id,
                                                'examiner_paper_id' => $paper_id,
                                                // 'examiner_bank_account_holder_name' =>  $bank_account_name,
                                                // 'examiner_bank_account_no' =>  $bank_account_no,
                                                // 'examiner_bank_ifsc' =>  $bank_ifsc,
                                                // 'examiner_bank_branch_name' =>  $bank_branch,
                                                'examiner_name' =>  $examiner_name,
                                                // 'examiner_phone' =>  $examiner_phone,
                                                // 'examiner_email' =>  $examiner_email,
                                                'examiner_user_id' =>  $new_user->u_id,
                                                'is_active' =>  '1',
                                                // 'examiner_paper_entry' => $request->paper_entry_type,
                                                // 'examiner_paper_type' => $request->paper_type,
                                                'map_paper_entry_type' => $request->paper_entry_type,
                                                'map_paper_type' => $request->paper_type,
                                            ]);
                                        }
                                    }
                                }

                                $inst_name = $ins_detail->institute_name;
                                auditTrail($user_id, "{$examiner_name} has updated as an internel examiner of {$inst_name}");

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Internal Examiner updated successfully"
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

    public function internalExamierList(Request $request)
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

                    if (in_array('internal-examiner-list', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'is_direct' => ['required'],
                            'inst_id' => ['nullable', Rule::requiredIf(!$request->is_direct)]
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            $is_direct = $request->is_direct;
                            $inst_id = $request->inst_id;

                            if ($is_direct) {
                                $users = User::where('u_role_id', 3)
                                    ->where('is_active', 1)
                                    ->where('is_direct', 1)
                                    ->get();

                                $final_list = $users->map(function ($user) {
                                    return [
                                        'examiner_id' => $user->u_id,
                                        'examiner_name' => $user->u_fullname
                                    ];
                                });
                            } else {
                                $internals = OtherDiplomaExaminnerInstitute::where('examiner_inst_id', $inst_id)
                                    ->where('is_active', 1)
                                    ->get();

                                $final_list = $internals->map(function ($internal) {
                                    return [
                                        'examiner_id' => $internal->examiner_user_id,
                                        'examiner_name' => $internal->examiner_name
                                    ];
                                });
                            }

                            if (sizeof($final_list) > 0) {
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Examiner List found',
                                    'count'         =>   sizeof($final_list),
                                    'data'   =>  $final_list
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

    public function defaultStudentAttendance()
    {
        $student_data = DB::table('wbscte_other_diploma_student_master_tbl')
            ->select('student_id_pk', 'student_reg_no', 'student_fullname', 'student_course_id', 'student_institute_name', 'student_institute_code', 'student_inst_id', 'student_semester')
            ->where(['student_is_enrolled' => 1, 'student_exam_fees_status' => 1, 'student_session_yr' => '2023-24', 'student_semester' => 'Semester I'])
            ->get();
        $finalList = $StudentcourseWisePaper =  [];
        if (sizeof($student_data) > 0) {
            foreach ($student_data as $key => $student_attendance) {
                $StudentcourseWisePaper[] = $this->courseWisePaper($student_attendance->student_course_id, $student_attendance->student_reg_no, $student_attendance->student_inst_id, $student_attendance->student_semester);
            }

            foreach ($StudentcourseWisePaper as $k => $val) {
                foreach ($val as $item) {
                    $finalList[] = $item;
                }
            }
        }
        try {
            DB::table('wbscte_other_diploma_attendence_tbl')->insert($finalList);
            return response()->json(
                array(
                    'error' => false,
                    'message' => 'All attendance data inserted'
                )
            );
        } catch (Exception $e) {
            return response()->json(
                array(
                    'error' => true,
                    'code' =>    'INT_00001',
                    'message' => $e->getMessage()
                )
            );
        }
    }

    public function courseWisePaper($course_id, $reg_no, $inst_id, $semester)
    {
        $student = [];
        $paperList = DB::table('wbscte_other_diploma_paper_master')
            ->select('paper_id_pk', 'paper_category', 'paper_code', 'paper_name', 'paper_category')
            ->where(['is_active' => 1, 'course_id' => $course_id, 'inst_id' => $inst_id, 'paper_affiliation_year' => '2023-24', 'paper_semester' => $semester])
            ->get();

        $now = date('Y-m-d H:i:s');
        if (sizeof($paperList) > 0) {
            foreach ($paperList as $key => $paper) {
                $student[$key]['att_reg_no'] = $reg_no;
                $student[$key]['att_inst_id'] = $inst_id;
                $student[$key]['att_course_id'] = $course_id;
                $student[$key]['att_paper_id'] = $paper->paper_id_pk;
                $student[$key]['att_sem'] = $semester;
                $student[$key]['att_paper_type'] = $paper->paper_category;
                $student[$key]['att_paper_entry_type'] = $paper->paper_category;
                $student[$key]['att_is_present'] = '1';
                $student[$key]['att_is_absent'] = '0';
                $student[$key]['att_is_ra'] = '0';
                $student[$key]['att_created_on'] = $now;
                $student[$key]['is_final_submit'] = 0;
                $student[$key]['attr_sessional_yr'] = '2023-24';
            }
            return $student;
        } else {
            return $student;
        }
    }

    public function internalExamierWiseInstituteList(Request $request)
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

                    if (in_array('internal-examinerwise-institute-list', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'is_direct' => ['required'],
                            'examiner_user_id' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            $is_direct = $request->is_direct;
                            $examiner_user_id = $request->examiner_user_id;
                            $examiner_inst_list = OtherDiplomaExaminnerInstitute::where('examiner_user_id', $examiner_user_id)
                                ->where('is_active', 1)
                                ->with('institute:inst_sl_pk,institute_name')
                                ->pluck('examiner_inst_id');

                            $instituteNames = Institute::whereIn('inst_sl_pk', $examiner_inst_list)->get();

                            if (sizeof($instituteNames) > 0) {
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'ExaminerWise Institute List found',
                                    'data'   =>  InstituteResource::collection($instituteNames)
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

    public function specialExaminerEntry(Request $request)
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

                    if (in_array('special-examiner-entry', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'examiner_name' => ['required'],
                            'u_phone' => 'required|digits:10|unique:wbscte_other_diploma_users_master,u_phone',
                            'examiner_email' => ['required', 'email'],
                            'bank_account_holder_name' => ['required'],
                            'bank_account_no' => ['required'],
                            'bank_ifsc' => ['required'],
                            'bank_branch_name' => ['required'],
                            'is_direct' => ['required'],
                        ], [
                            'u_phone.unique' => 'Phone already taken'
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }

                        try {
                            $is_direct = $request->is_direct;
                            $examiner_name = $request->examiner_name;
                            $examiner_phone = $request->u_phone;
                            $examiner_email = $request->examiner_email;
                            $bank_account_name = $request->bank_account_holder_name;
                            $bank_account_no = $request->bank_account_no;
                            $bank_branch = $request->bank_branch_name;
                            $bank_ifsc = $request->bank_ifsc;
                            $user_role_id = 3;

                            DB::beginTransaction();

                            if ($is_direct) {
                                $new_user = User::create([
                                    'u_ref' => generateRandomCode(),
                                    'u_fullname' => $examiner_name,
                                    'u_phone' => $examiner_phone,
                                    'u_email' => $examiner_email,
                                    'bank_account_holder_name' => $bank_account_name,
                                    'bank_account_no' => $bank_account_no,
                                    'bank_ifsc' => $bank_ifsc,
                                    'bank_branch_name' => $bank_branch,
                                    'u_role_id' => $user_role_id,
                                    'is_active' => 1,
                                    'is_direct' => $is_direct,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ]);
                            }

                            DB::commit();

                            if ($new_user) {
                                auditTrail($user_id, "{$examiner_name} has added as an user");

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  " Special examiner entry successfully"
                                    
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

    public function specialExaminerTag(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('special-examiner-tag', $url_data)) {
                        $validated = Validator::make($request->all(), [
                            'semester' => ['required'],
                            'session_year' => ['required'],
                            'paper_type' => ['required'],
                            'course_id' => ['required'],
                            'paper_id' => ['required'],
                            'examiner_id' => ['required'],
                            'examiner_name' => ['required'],
                            'taggable_inst_id' => ['required'],
                            'paper_entry_type' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }

                        try {
                            $semester = $request->semester;
                            $session = $request->session_year;
                            $paper_type = $request->paper_type;
                            $course_id = $request->course_id;
                            $paper_id = $request->paper_id;
                            $examiner_id = $request->examiner_id;
                            $examiner_name = $request->examiner_name;
                            $taggable_inst_id = $request->taggable_inst_id;
                            $paper_entry_type = $request->paper_entry_type;

                            $examinerTags = ExternelExaminerMap::query();

                            $same_examiner = $examinerTags->clone()
                                ->where([
                                    'map_assign_inst_id' => $request->taggable_inst_id,
                                    'map_examiner_id' => $request->examiner_id,
                                    'map_course_id' => $request->course_id,
                                    'map_paper_id' => $request->paper_id,
                                    'map_paper_entry_type' => $paper_entry_type,
                                    'map_paper_type' => $paper_type,
                                    'map_sem' => $request->semester
                                ])
                                ->first();

                            $examiner_exist = $examinerTags->clone()
                                ->where([
                                    'map_assign_inst_id' => $request->taggable_inst_id,
                                    'map_course_id' => $request->course_id,
                                    'map_paper_id' => $request->paper_id,
                                    'map_paper_entry_type' => $paper_entry_type,
                                    'map_paper_type' => $paper_type,
                                    'map_sem' => $request->semester
                                ])
                                ->first('map_examiner_id');

                            if ($same_examiner) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists same!"
                                ], 200);
                            } else if ($examiner_exist) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists Examiner!"
                                ], 200);
                            } else {
                                DB::beginTransaction();

                                $value = ExternelExaminerMap::create([
                                    'map_paper_id' => $paper_id,
                                    'map_paper_type' => $paper_type,
                                    'map_examiner_id' => $examiner_id,
                                    'map_examiner_name' => $examiner_name,
                                    'map_paper_entry_type' => $paper_entry_type,
                                    'map_course_id' => $course_id,
                                    'map_sem' => $semester,
                                    'map_affiliation_year' => $session,
                                    'is_active' => 1,
                                    'map_assign_inst_id' => $taggable_inst_id,
                                    'created_on' => now(),
                                    'updated_on' => now(),
                                    'created_by' => $user_id,
                                    'updated_by' => $user_id
                                ]);

                                DB::commit();

                                if ($value) {
                                    $ext_inst_name = Institute::find($taggable_inst_id, 'institute_name')->institute_name;
                                    auditTrail($user_id, "{$examiner_name} has tagged as an external examiner of {$ext_inst_name}");

                                    return response()->json([
                                        'error' =>  false,
                                        'message' =>  "Special examiner tagged successfully",
                                        'lists' => new SpecialExaminerResource($value)
                                    ], 200);
                                }
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
    public function deleteInternalExaminer(Request $request, $id = null){
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('special-examiner-tag', $url_data)) {
                        if (is_null($id)) {
                            throw ValidationException::withMessages(['id' => 'id is required as path param']);
                        }
                        $delete_data = OtherDiplomaExaminnerInstitute::where('examiner_id',$id)->delete();
                        if ($delete_data) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner Data deleted',
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
     public function internalExaminerDetails(Request $request,$examiner_id = null){
        // dd('hi');
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

                    if (in_array('internal-examiner-details', $url_data)) { //check url has permission or not

                             $examiner = OtherDiplomaExaminnerInstitute::where('examiner_id',$examiner_id)
                             ->select('examiner_phone','examiner_email','examiner_bank_account_holder_name','examiner_bank_account_no','examiner_bank_ifsc','examiner_bank_branch_name')
                             ->first();

                              if ($examiner) {
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Examiner details found',
                                    'data'   =>  [
                                        'phone' => $examiner->examiner_phone,
                                        'email' => $examiner->examiner_email,
                                        'account_name' => $examiner->examiner_bank_account_holder_name,
                                        'account_no' => $examiner->examiner_bank_account_no,
                                        'account_ifsc' => $examiner->examiner_bank_ifsc,
                                        'account_branch' => $examiner->examiner_bank_branch_name
                                    ]
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
    public function updateInternalExaminerEntry(Request $request){
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('internal-examiner-info-update', $url_data)) {
                        $user_id = $request->examiner_user_id;
                        $user_data = User::find($user_id);


                        $validated = Validator::make($request->all(), [

                            'examiner_user_id' => ['required'],
                            'examiner_name' => ['required'],
                            // 'semester' => ['required'],
                            'u_phone' => ['required', Rule::unique('wbscte_other_diploma_users_master', 'u_phone')->ignore($user_data->u_id, 'u_id')],
                            'examiner_email' => ['required', 'email'],
                            'bank_account_holder_name' => ['required'],
                            'bank_account_no' => ['required'],
                            'bank_ifsc' => ['required'],
                            'bank_branch_name' => ['required'],

                        ], [
                            'u_phone.unique' => 'Phone already taken'
                        ]);
                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        try {
                             DB::beginTransaction();

                                $examiner_name = $request->examiner_name;
                                $examiner_phone = $request->u_phone;
                                $examiner_email = $request->examiner_email;
                                $examiner_bank_account_holder_name = $request->bank_account_holder_name;
                                $examiner_bank_account_no = $request->bank_account_no;
                                $examiner_bank_ifsc = $request->bank_ifsc;
                                $examiner_bank_branch_name = $request->bank_branch_name;
                                $active_status = $request->is_active;


                                $user_data->u_fullname = $examiner_name;
                                $user_data->u_phone = $examiner_phone;
                                $user_data->u_email = $examiner_email;
                                $user_data->bank_account_holder_name = $examiner_bank_account_holder_name;
                                $user_data->bank_account_no = $examiner_bank_account_no;
                                $user_data->bank_branch_name = $examiner_bank_branch_name;
                                $user_data->bank_ifsc = $examiner_bank_ifsc;
                                $user_data->is_active = $active_status;

                                $user_data->save();
                                $examiner_details = OtherDiplomaExaminnerInstitute::where('examiner_user_id',$user_id)->update([
                                    'examiner_name'=> $examiner_name,
                                    'examiner_phone'=> $examiner_phone,
                                    'examiner_email'=> $examiner_email,
                                    'examiner_bank_account_holder_name'=>$examiner_bank_account_holder_name,
                                    'examiner_bank_ifsc' => $examiner_bank_account_no,
                                    'examiner_bank_account_no'=> $examiner_bank_branch_name,
                                    'examiner_bank_branch_name'=>$examiner_bank_ifsc,
                                    'is_active'=>$active_status,

                                ]);
                                // dd($examiner_details);

                                DB::commit();
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Examiner details Updated Successfully',
                                );
                                return response(json_encode($reponse), 200);

                            }catch (Exception $e) {
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
    public function InternalExaminerAssignList(Request $request,$session_yr = null,$semester = null){
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

                    if (in_array('internal-examiner-assign-list', $url_data)) {
                        if($semester == null || $session_yr == null){
                                $examinerTagsList = OtherDiplomaExaminnerInstitute::query();
                            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('assign_status',1)->orderBy('examiner_id', 'DESC')->get();
                            } else if ($user_data->u_role_id == '2') { //Institute Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('examiner_inst_id', $user_data->u_inst_id)->orderBy('examiner_id', 'DESC')->get();
                            }
                        }else{
                            $examinerTagsList = OtherDiplomaExaminnerInstitute::query();
                            if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('assign_status',1)->whereHas('course', function ($query) use ($session_yr) {
                                    $query->where('course_affiliation_year', $session_yr);
                                })->where('examiner_part_sem',$semester)->orderBy('examiner_id', 'DESC')->get();
                            } else if ($user_data->u_role_id == '2') { //Institute Admin
                                $examiner_tag_list = $examinerTagsList->clone()->with(['course', 'paper', 'user'])->where('examiner_inst_id', $user_data->u_inst_id)->where('assign_status',1)->whereHas('course', function ($query) use ($session_yr) {
                                    $query->where('course_affiliation_year', $session_yr);
                                })->where('examiner_part_sem',$semester)->orderBy('examiner_id', 'DESC')->get();
                            }
                        }
                        // return $examiner_tag_list;
                        if (sizeof($examiner_tag_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner List found',
                                'count'         =>   sizeof($examiner_tag_list),
                                'lists'   =>  ExaminerInternalResource::collection($examiner_tag_list)
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
    public function specialExaminerList(Request $request,$session_yr = null,$semester = null){
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

                    if (in_array('special-examiner-list', $url_data)) { //check url has permission or not
                        // $special_examinerList = ExternelExaminerMap ::whereNull('map_source_inst_id')->get();
                    //  return $special_examinerList;
                    if($semester == null || $session_yr == null){

                         $special_examinerList = ExternelExaminerMap::query();
                         if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $specialexaminer_tag_list = $special_examinerList->clone()->with(['course', 'paper'])->whereNull('map_source_inst_id')->get();
                         }
                    }else{
                        $special_examinerList = ExternelExaminerMap::query();
                         if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $specialexaminer_tag_list = $special_examinerList->clone()->with(['course', 'paper'])->whereNull('map_source_inst_id')->where('map_affiliation_year',$session_yr)->where('map_sem',$semester)->get();
                         }

                    }     
                        // return $examiner_tag_list;
                        if (sizeof($specialexaminer_tag_list) > 0) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner List found',
                                'count'         =>   sizeof($specialexaminer_tag_list),
                                'lists'   =>  ExternelExaminerResource::collection($specialexaminer_tag_list)
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

}
