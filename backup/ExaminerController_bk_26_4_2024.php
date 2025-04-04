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
use App\Models\wbscte\TheorySubject;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\ExternelExaminerMap;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\wbscte\InstituteResource;
use App\Models\wbscte\OtherDiplomaExaminnerInstitute;
use App\Http\Resources\wbscte\ExaminerInternalResource;
use App\Http\Resources\wbscte\ExternelExaminerResource;

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
                            'semester' => ['required'],
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
                            $semester = $request->semester;
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
                                'u_role_id' => $user_role_id,
                                'is_active' => 1,
                                'is_direct' => $is_direct,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            if (!$is_direct) {
                                $ins_detail = Institute::find($request->inst_id);

                                OtherDiplomaExaminnerInstitute::create([
                                    'examiner_user_id' => $new_user->u_id,
                                    'examiner_inst_id' => $inst_id,
                                    'examiner_inst_code' => $ins_detail->institute_code,
                                    'examiner_part_sem' => $semester,
                                    'examiner_bank_account_holder_name' => $bank_account_name,
                                    'examiner_bank_account_no' => $bank_account_no,
                                    'examiner_bank_ifsc' => $bank_ifsc,
                                    'examiner_bank_branch_name' => $bank_branch,
                                    'examiner_name' => $examiner_name,
                                    'examiner_phone' => $examiner_phone,
                                    'examiner_email' => $examiner_email,
                                    'is_active' => 0,
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
                                'message'              =>  "Examiner entry successfully"
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
                            'paper_id' => 'required'

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
                            $check = $examinerTags->clone()->where(['examiner_inst_id' => $request->inst_id, 'examiner_user_id' => $request->examiner_id, 'examiner_course_id' => $request->course_id, 'examiner_paper_id' => $request->paper_id, 'examiner_part_sem' => $request->semester])->first();
                            $check2 = $examinerTags->clone()->where(['examiner_inst_id' => $request->inst_id,  'examiner_course_id' => $request->course_id, 'examiner_paper_id' => $request->paper_id, 'examiner_part_sem' => $request->semester])->first('examiner_user_id');


                            if ($check) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists same!"
                                ], 200);
                            } else if ($check2) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists Examiner!"
                                ], 200);
                            } else {


                                $a = $examinerTags->clone()->where(['is_active' => 0, 'examiner_inst_id' => $request->inst_id, 'examiner_user_id' => $request->examiner_id])->first();

                                if ($a) {
                                    $a->update([
                                        'examiner_course_id'           =>  $request->course_id,
                                        'examiner_paper_id'    =>  $request->paper_id,
                                        'is_active' => '1'
                                    ]);
                                } else {
                                    $ins_detail = Institute::find($request->inst_id);
                                    $examiner_details = $examinerTags->clone()->where('examiner_user_id', $request->examiner_id)->first();
                                    //dd($request->all());
                                    OtherDiplomaExaminnerInstitute::create([
                                        'examiner_course_id'           =>  $request->course_id,
                                        'examiner_paper_id'    =>  $request->paper_id,
                                        'examiner_user_id'     => $request->examiner_id,
                                        'examiner_inst_id' => $request->inst_id,
                                        'examiner_inst_code' => $ins_detail->institute_code,
                                        'examiner_part_sem' => $request->semester,
                                        'examiner_bank_account_holder_name' => $examiner_details->bank_account_holder_name,
                                        'examiner_bank_account_no' => $examiner_details->bank_account_no,
                                        'examiner_bank_ifsc' => $examiner_details->bank_ifsc,
                                        'examiner_bank_branch_name' => $examiner_details->bank_branch_name,
                                        'examiner_name' => $examiner_details->examiner_name,
                                        'examiner_phone' => $examiner_details->examiner_phone,
                                        'examiner_email' => $examiner_details->examiner_email,
                                        'is_active' => '1'
                                    ]);
                                }
                            }

                            DB::commit();
                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Examiner taged successfully"
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

    public function internalExaminerTagList(Request $request)
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

                    if (in_array('internal-examiner-tag-list', $url_data)) { //check url has permission or not
                        $examinerTagsList = OtherDiplomaExaminnerInstitute::query();
                        if ($user_data->u_role_id == '1') { //Super Admin or Council Admin
                            $examiner_tag_list = $examinerTagsList->clone()->get();
                        } else if ($user_data->u_role_id == '2') { //Institute Admin
                            $examiner_tag_list = $examinerTagsList->clone()->where('examiner_inst_id', $user_data->u_inst_id)->get();
                        }
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

    public function internalExaminerTagShow(Request $request, $id = null)
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

                    if (in_array('internal-examiner-tag-show', $url_data)) { //check url has permission or not

                        if (is_null($id)) {
                            throw ValidationException::withMessages(['id' => 'id is required as path param']);
                        }
                        $examinerTagDetails = OtherDiplomaExaminnerInstitute::find($id);
                        if ($examinerTagDetails) {
                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Examiner Details found',
                                'details'   =>  $examinerTagDetails
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
                            'inst_id' => ['required']
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        } else {
                            $inst_id = $request->inst_id;

                            $inst_list = Institute::where('is_active', 1)
                                ->whereNotIn('inst_sl_pk', [$inst_id])
                                ->get();

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

                                    $ext_inst_name = Institute::find($inst_tag_id, 'institute_name')->institute_name;

                                    if ($is_direct) {
                                        auditTrail($user_id, "{$examiner_name} has {$txt} as an externel examiner of {$ext_inst_name}");
                                    } else {
                                        auditTrail($user_id, "{$examiner_name} has {$txt} as an externel examiner of {$ext_inst_name} who is an internel examiner of {$source_inst_name}");
                                    }
                                }

                                DB::commit();

                                $ext_list = ExternelExaminerMap::select('map_id', 'map_examiner_name', 'map_source_inst_id', 'map_assign_inst_id', 'map_sem', 'map_affiliation_year', 'map_paper_type', 'map_course_id', 'map_paper_id', 'map_examiner_id', 'is_active')
                                    ->where([
                                        'map_sem' => $semester,
                                        'map_affiliation_year' => $session,
                                        'map_paper_type' => $paper_type,
                                        'map_course_id' => $course_id,
                                        'map_paper_id' => $paper_id,
                                        'map_examiner_id' => $examiner_id,
                                        'is_active' => 1
                                    ])
                                    ->with('internelInstituteName:inst_sl_pk,institute_name', 'ExternerInstituteName:inst_sl_pk,institute_name')
                                    ->get();

                                if (sizeof($ext_list) > 0) {
                                    $reponse = array(
                                        'error'         =>  false,
                                        'message'       =>  "Externel Examiner Tag {$txt}",
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

    public function externelExaminerList(Request $request)
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

                        $ext_list = ExternelExaminerMap::select('map_id', 'map_examiner_name', 'map_source_inst_id', 'map_assign_inst_id')
                            ->with('internelInstituteName:inst_sl_pk,institute_name', 'ExternerInstituteName:inst_sl_pk,institute_name')
                            ->get();

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
                $user_data = User::select('u_id', 'u_ref', 'u_role_id', 'u_inst_id')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('internal-examiner-update', $url_data)) { //check url has permission or not
                        $examinerTags = OtherDiplomaExaminnerInstitute::query();
                        $examiner_id = $request->examiner_id;
                        $examiner_data = $examinerTags->clone()->find($examiner_id);
                        $new_user = User::find($examiner_data->examiner_user_id);

                        $validated = Validator::make($request->all(), [
                            'inst_id' => ['required'],
                            'examiner_id' => ['required'],
                            'course_id' => ['required'],
                            'paper_id' => ['required'],
                            'examiner_name' => ['required'],
                            'semester' => ['required'],
                            'u_phone' => ['required', Rule::unique('wbscte_other_diploma_users_master', 'u_phone')->ignore($new_user->u_id, 'u_id')],
                            'examiner_email' => ['required', 'email'],
                            'bank_account_holder_name' => ['required'],
                            'bank_account_no' => ['required'],
                            'bank_ifsc' => ['required'],
                            'bank_branch_name' => ['required'],
                            'is_confirmed' => ['required'],
                        ], [
                            'u_phone.unique' => 'Phone already taken'
                        ]);

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
                                $examiner_phone = $request->u_phone;
                                $examiner_email = $request->examiner_email;
                                $bank_account_name = $request->bank_account_holder_name;
                                $bank_account_no = $request->bank_account_no;
                                $bank_branch = $request->bank_branch_name;
                                $bank_ifsc = $request->bank_ifsc;
                                $is_confirmed = $request->is_confirmed;

                                $ins_detail = Institute::find($request->inst_id);

                                $new_check = $examinerTags->clone()
                                    ->where([
                                        'examiner_inst_id' => $request->inst_id,
                                        'examiner_user_id' => $examiner_data->examiner_user_id,
                                        'examiner_course_id' => $request->course_id,
                                        'examiner_part_sem' => $request->semester,
                                        'examiner_id' => $examiner_id
                                    ])
                                    ->first();

                                $new_user->update([
                                    'u_fullname' => $examiner_name,
                                    'u_phone' => $examiner_phone,
                                    'u_email' => $examiner_email,
                                    'updated_at' => $now,
                                ]);

                                $new_check->update([
                                    'examiner_bank_account_holder_name' =>  $bank_account_name,
                                    'examiner_bank_account_no' =>  $bank_account_no,
                                    'examiner_bank_ifsc' =>  $bank_ifsc,
                                    'examiner_bank_branch_name' =>  $bank_branch,
                                    'examiner_name' =>  $examiner_name,
                                    'examiner_phone' =>  $examiner_phone,
                                    'examiner_email' =>  $examiner_email,
                                ]);

                                $is_old_found = false;

                                $old_check = $examinerTags->clone()
                                    ->where([
                                        'examiner_inst_id' => $request->inst_id,
                                        'examiner_course_id' => $request->course_id,
                                        'examiner_paper_id' => $request->paper_id,
                                        'examiner_part_sem' => $request->semester,
                                    ])
                                    ->whereNot('examiner_id', $examiner_id)
                                    ->first();

                                $old_user = $old_check;

                                if ($old_check && !$is_confirmed) {
                                    $is_old_found = true;

                                    return response()->json([
                                        'error' => true,
                                        'is_old_found' => $is_old_found,
                                        'message' => "Examiner already exists for this paper"
                                    ], 200);
                                }

                                if ($is_confirmed) {
                                    $res = $old_user->delete();

                                    if ($res) {
                                        $new_check->update([
                                            'examiner_inst_id' => $inst_id,
                                            'examiner_inst_code' =>  $ins_detail->institute_code,
                                            'examiner_part_sem' =>  $semester,
                                            'examiner_course_id' => $course_id,
                                            'examiner_paper_id' => $paper_id
                                        ]);
                                    }
                                }

                                $inst_name = $ins_detail->institute_name;
                                auditTrail($user_id, "{$examiner_name} has updated as an internel examiner of {$inst_name}");

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Internel Examiner updated successfully"
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
}
