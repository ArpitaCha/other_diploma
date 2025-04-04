<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use App\Models\wbscte\Student;
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
	public function authenticate(Request $request) 
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $u_username = $request->user_phone;
        $user_type = $request->user_type;
    
        // Fetch user based on type
        if ($user_type === 'STUDENT') {
            $user = Student::where('student_mobile_no', $u_username)->first();
            $to_phone = $user->student_mobile_no ?? null; // Ensure correct field
        } else {
            $user = User::where('u_phone', $u_username)->where('is_active', 1)->first();
            $to_phone = $user->u_phone ?? null; // Ensure correct field
        }
    
        if ($user === null || $to_phone === null) {
            return response()->json([
                'error' => true,
                'message' => 'Mobile Number/Profile not registered.'
            ], 404);
        }

        // Generate OTP
        $otp = env('APP_ENV') === 'local' ? 123456 : rand(111111, 999999);
    
        try {
            // Check if OTP entry already exists
            $otp_record = DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->first();
    
            if ($otp_record) {
                $last_otp_date = substr(trim($otp_record->otp_created_on), 0, 10);
                if ($last_otp_date == $today) {
                    $minutes = getTimeDiffInMinute($now, $otp_record->otp_created_on);
    
                    if ($otp_record->otp_count < 9) {
                        if ($minutes > 2) {
                            // Send OTP
                            $sms_message_user = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";
                            send_sms($to_phone, $sms_message_user);
    
                            // Update OTP record
                            DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->update([
                                'otp' => $otp,
                                'otp_created_on' => $now,
                                'otp_count' => intval($otp_record->otp_count) + 1
                            ]);
    
                            $otp_sent = true;
                        } else {
                            return response()->json([
                                'error' => true,
                                'message' => 'Your previous OTP was generated in the last 2 minutes.'
                            ], 401);
                        }
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => 'You have exceeded the OTP generation limit for today. Try again tomorrow.'
                        ], 401);
                    }
                } else {
                    // New day, reset OTP count
                    send_sms($to_phone, "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD");
    
                    DB::table('wbscte_other_diploma_otp_tbl')->where('username', $to_phone)->update([
                        'otp' => $otp,
                        'otp_created_on' => $now,
                        'otp_count' => 1
                    ]);
    
                    $otp_sent = true;
                }
            } else {
                // No previous OTP entry, create new one
                send_sms($to_phone, "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD");
    
                DB::table('wbscte_other_diploma_otp_tbl')->insert([
                    'username' => $to_phone,
                    'otp' => $otp,
                    'otp_created_on' => $now,
                    'otp_count' => 1
                ]);
    
                $otp_sent = true;
            }
    
            if ($otp_sent) {
                $otp_exp_time = date('Y-m-d H:i:s', strtotime('+120 seconds', strtotime($now)));
    
                return response()->json([
                    'error' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp,
                    'otp_expire_time' => formatDate($otp_exp_time, 'Y-m-d H:i:s', 'M j, Y H:i:s')
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 400);
        }

        }

        
  
    
	//Validate OTP during Login
    public function validateSecurityCode(Request $request) {
        $now        =   date('Y-m-d H:i:s');
        $u_username =   $request->user_phone;
        $u_otp      =   $request->security_code;
        $user_type = $request->user_type;
       

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
                if ($user_type === 'STUDENT') {
                    $user = Student::with(['role:role_id,role_name,role_description'])
                    ->where('student_mobile_no', $u_username)
                    ->first();
                } else {
                    $user = User::with(['role:role_id,role_name,role_description'])
                                ->where('u_phone', $u_username)
                                ->where('is_active', 1)
                                ->first();
                }
          
                if ($user) {
                        $token  =   md5($now . rand(10000000, 99999999));
                        $expiry =   date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($now)));
                        $user_id = $user_type === 'STUDENT' ? $user->student_id_pk : $user->u_id;
                        DB::table('wbscte_other_diploma_tokens')->where('t_user_id', $user_id)->delete();
                        $token_data   =   array(
                            't_token'           =>  $token,
                            't_generated_on'    =>  $now,
                            't_expired_on'      =>  $expiry,
                            't_user_id'         =>  $user_id ,
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
