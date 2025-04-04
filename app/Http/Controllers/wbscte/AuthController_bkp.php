<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    protected $auth;
    public $back_url = null;

    public function __construct()
    {
        //$this->auth = new Authentication();
    }
    public function authenticate(Request $request)
    {
        $now  =   date('Y-m-d H:i:s');
        $request->validate([
            'user_name' => 'required',
            'user_password' => 'required',
            // 'g_recaptcha_response' => ['required', new ReCaptcha]
        ]);
        $user_name      =   $request->user_name;
        $password       =   $request->user_password;
        $converted_pw   =   hash("sha512", $password);
        DB::beginTransaction();
        try {
            $user = User::with(['role:role_id,role_name,role_description'])
                ->where('u_username', $user_name)->where('is_active', '1')->first();
            if ($user) {
                if ($user->u_password == $converted_pw) {
                    $token  =   md5($now . rand(10000000, 99999999));
                    $expiry =   date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($now)));
                    //delete previous token if any
                    DB::table('wbscte_other_diploma_tokens')->where('t_user_id', $user->u_id)->delete();
                    //insert token
                    $token_data   =   array(
                        't_token'           =>  $token,
                        't_generated_on'    =>  $now,
                        't_expired_on'      =>  $expiry,
                        't_user_id'         =>  $user->u_id,
                    );
                    Token::create($token_data);
                    $role_url_access_id = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user->u_role_id)->pluck('rp_url_id');
                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get();
                    } else {
                        $urls = array();
                    }
                    DB::commit();
                    return response()->json([
                        'error'             =>  false,
                        'token'             =>  $token,
                        'token_expired_on'  =>  $expiry,
                        //'urls'              =>  json_encode($urls),
                        'user'              =>  json_encode($user)
                    ], 200);
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  'Either your username or password is wrong'
                    ], 404);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Either your username or password is wrong'
                ], 404);
            }
        } catch (Exception $e) {
            DB::rollBack();
            generateLaravelLog($e);
            return response()->json(
                array(
                    'error' => true,
                    'code' =>    'INT_00001',
                    'message' => 'Unable to send request'
                )
            );
        }
    }
}
