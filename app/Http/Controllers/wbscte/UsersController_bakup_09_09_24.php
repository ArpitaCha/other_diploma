<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\wbscte\Token;
use App\Models\wbscte\User;
use Exception;
use Validator;
use DB;

use App\Http\Resources\wbscte\UserResource;

class UsersController extends Controller
{

    protected $auth;
    public $back_url = null;

    public function __construct()
    {
        //$this->auth = new Authentication();
    }

    public function allUsers(Request $request, $ct_id = NULL)
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

                    if (in_array('user-list', $url_data)) { //check url has permission or not
                        if ($ct_id != null) {
                            $user_list = User::where('u_vtc_code', $ct_id)->where('is_active', '1')->with(['role:role_id,role_name'])->orderBy('u_role_id', 'ASC')
                                ->orderBy('u_fullname', 'ASC')->get();
                        } else {

                            $user_list = User::where('is_active', '1')->where('u_id', '>=', 1)->with(['role:role_id,role_name'])->orderBy('u_role_id', 'ASC')->orderBy('u_fullname', 'ASC')->get();
                        }
                        //echo($user_list);exit(); 
                        if (sizeof($user_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'User found',
                                'count'     =>  sizeof($user_list),
                                'users'     =>  UserResource::collection($user_list)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No users available'
                            );
                            return response(json_encode($reponse), 404);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>  "Oops! you don't have sufficient permission"
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  "Oops! you don't have sufficient permission"
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

    public function createUser(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    //'access_urls' => 'required|array',
                    'user_name'   => 'required',
                    'password'    => 'required',
                    'fullname'    => 'required',
                    'email'       => 'required',
                    'phone'       => 'required',
                    'u_role_id'   => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages()
                    ], 400);
                } else {
                    $token_user_id = $token_check->t_user_id;
                    $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
                    $role_url_access_id = DB::table('wctc_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('wctc_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');

                        if (in_array('users/create', $url_data)) { //check url has permission or not
                            DB::beginTransaction();
                            try {
                                $role_id = $request->u_role_id;
                                $user_name = $request->user_name;
                                $password =  hash("sha512", $request->password);
                                $fullname = $request->fullname;
                                $email = $request->email;
                                $phone = $request->phone;
                                // $res= $request->u_ct_ref;
                                // dd($res);


                                $user_data = new User;
                                $user_data->u_ref      =  md5($now . rand(6, 9));
                                $user_data->u_username =  $user_name;
                                $user_data->u_password =  $password;
                                $user_data->u_fullname =  $fullname;
                                $user_data->u_phone =   $phone;
                                $user_data->u_email =   $email;
                                $user_data->u_role_id  =  $role_id;

                                // die;

                                if ($user_data->u_role_id  == '5') {
                                    $center = Center::where('ct_id', $request->center_id)->first();
                                    $user_data->u_ct_id = $center->ct_id;
                                }


                                $user_data->save();
                                DB::commit();

                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  'User created successfully'
                                ], 200);
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

    public function editUser(Request $request, $ref_code)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $token_user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
                $role_url_access_id = DB::table('wctc_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wctc_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('users/edit', $url_data)) { //check url has permission or not
                        $users = User::where('u_ref', $ref_code)->where('is_active', '1')->with(['employee', 'role:role_id,role_name'])->orderBy('u_id', 'DESC')->get();

                        if ($users) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'User found',
                                'count'     =>  sizeof($users),
                                'users'  =>  json_encode($users)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No user available with this ref code!'
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

    public function updateUser(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $validator = Validator::make($request->all(), [
                    'user_name'   => 'required',
                    'fullname'    => 'required',
                    'email'       => 'required',
                    'phone'       => 'required',
                    'u_role_id'   => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  $validator->messages()
                    ], 400);
                } else {
                    $token_user_id = $token_check->t_user_id;
                    $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();

                    $role_url_access_id = DB::table('wctc_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('wctc_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');

                        //if (in_array('users/update', $url_data)) {//check url has permission or not
                        DB::beginTransaction();
                        try {
                            $user = User::where('u_ref', $request->user_ref)->first();

                            if ($user) {
                                $user_id = $user->u_id;
                                //$user->u_username =  $request->user_name;
                                $user->u_role_id  =  $request->u_role_id;
                                $user->u_fullname = $request->fullname;
                                $user->u_phone = $request->phone;
                                $user->u_email = $request->email;

                                $user->save(); //employee update pending pore korbo
                                DB::commit();

                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  'User updated successfully'
                                ], 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'message'   =>  'User not found'
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
                    /* }
                    else{
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>  "Oops! you don't have sufficient permission"
                        ], 401);
                    }     */
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

    public function viewUser(Request $request, $user_ref)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $token_user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
                $role_url_access_id = DB::table('wctc_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wctc_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('users/view', $url_data)) { //check url has permission or not
                        $user = User::where('u_ref', $user_ref)->where('is_active', '1')->with(['role:role_id,role_name'])->first();

                        if ($user) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'User found',
                                'user'  =>  $user
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No user available with this ref code!'
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

    //change password
    public function changePassword(Request $request, $user_ref)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $token_user_id = $token_check->t_user_id;
                $user_data = User::select('u_id', 'u_ref', 'u_role_id')->where('u_id', $token_user_id)->first();
                $role_url_access_id = DB::table('wctc_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('wctc_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('users/view', $url_data)) { //check url has permission or not
                        $user = User::where('u_ref', $user_ref)->where('is_active', '1')->first();

                        if ($user->u_ref == $user_ref) {

                            $password =  hash("sha512", $request->password);
                            $user->u_password = $password;

                            if ($user->u_ct_id != null) {
                                $user->is_default_pwd = 1;
                            }

                            $user->save();

                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Password changed successfully',
                                'user'  =>  $user
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No user available with this ref code!'
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
}
