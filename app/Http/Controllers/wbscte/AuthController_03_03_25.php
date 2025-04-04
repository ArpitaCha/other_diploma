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
	
	//Send OTP for authentication
	public function authenticate(Request $request) {
        $now        =   date('Y-m-d H:i:s');
		$today		=    date('Y-m-d');
        $u_username =   $request->user_phone;

        $user = User::where('u_phone', $u_username)->where('is_active', 1)->first();

        if($user === null) {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Mobile Number/Profile not registered.'
            ], 404);
        }
        else {
            //send email with OTP
            $otp = rand(111111,999999);
			$to_phone = $user->u_phone;

            try {
				if (DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->exists()) {
						$otp_res = DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->first();
						$last_otp_date = substr(trim($otp_res->otp_created_on), 0, 10);
					if ($last_otp_date == $today) {
							$minutes = getTimeDiffInMinute($now, $otp_res->otp_created_on);
							if ($otp_res->otp_count < 9) {
                                if ($minutes > 2) {
									$sms_message_user = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";
									$send_sms_user = send_sms($to_phone, $sms_message_user);
                                   DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->delete();
                                   DB::table('wbscte_other_diploma_otp_tbl')->insert(
                                    [
                                       'username' => $to_phone, 'otp' => $otp,
                                       'otp_created_on' => $now, 'otp_count' => intval($otp_res->otp_count) + 1
                                    ]
                                    );
                                    $otp_send = true;
                                } else {
                                         $last_otp_time = $otp_res->otp_created_on;
                                         $otp_exp_time  =date('Y-m-d H:i:s', strtotime('+120 seconds', strtotime($last_otp_time)));
                                         $otp_expire_time = formatDate($otp_exp_time, 'Y-m-d H:i:s', 'M j, Y H:i:s');
                                         $error_type = "otp_time";
										 $error_message = "Your previous OTP was generated in last 2 minutes";
                                            return response()->json([
                                                'error'     =>  true,
                                                'message'   =>  $error_message
                                            ], 401);
                                        }
                                    } else {
                                        $error_type = "otp_exceed";
                                        $error_message = "You exceed the OTP generation limit for today. Try again tomorrow.";
                                        return response()->json([
                                            'error'     =>  true,
                                            'message'   =>   $error_message
                                        ], 401);
                                    }
                                } else {
                                    $sms_message_user = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";
									$send_sms_user = send_sms($to_phone, $sms_message_user);
                                    DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->delete();
                                    DB::table('wbscte_other_diploma_otp_tbl')->insert(
                                        [
                                            'username' => $to_phone, 'otp' => $otp,
                                            'otp_created_on' => $now, 'otp_count' => 1
                                        ]
                                    );
                                    $otp_send = true;
                                }
					}else {
                       $otp_send = true;
					   $sms_message_user = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";
					   $send_sms_user = send_sms($to_phone, $sms_message_user);
                       DB::table('wbscte_other_diploma_otp_tbl')->insert(['username' => $to_phone, 'otp' => $otp, 'otp_created_on' => $now, 'otp_count' => 1]);
                      }
				if ($otp_send){
                                $otp_exp_time  = date('Y-m-d H:i:s', strtotime('+120 seconds', strtotime($now)));
                                $otp_expire_time = formatDate($otp_exp_time, 'Y-m-d H:i:s', 'M j, Y H:i:s');
                                $reponse = array(
                                    'error'         =>  false,
                                    'message'       =>  'Otp sent Successfully',
                                    'otp' => $otp,
                                    'otp_expire_time' => $otp_expire_time
                                );
                                return response(json_encode($reponse), 200);
                            }
            }
            catch(Exception $e) {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  $e->getMessage()
                ], 400);
            }

        }

        
    }
    
	//Validate OTP during Login
    public function validateSecurityCode(Request $request) {
        $now        =   date('Y-m-d H:i:s');
        $u_username =   $request->user_phone;
        $u_otp      =   $request->security_code;

        $otp = DB::table('wbscte_other_diploma_otp_tbl')
                ->where('username', $u_username)
                ->where('otp', $u_otp)
                ->first();

        if($otp === null) {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Either Phone number and/or security code does not match'
            ], 400);
        }     
        else {
			DB::beginTransaction();
			try {
           $user = User::with(['role:role_id,role_name,role_description'])
                ->where('u_phone', $u_username)->where('is_active', '1')->first();
            if ($user) {
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
					//dd($role_url_access_id);
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
                    'message'   =>  'OTP is wrong'
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

    public function logout($user_id){
        Token::where('t_user_id',$user_id)->delete();
        return response()->json([
            'error'=> false,
            'message'=> 'Logout successfully',

        ]);
    }  
  
}
