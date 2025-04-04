<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Notification;
use App\Http\Resources\wbscte\NotificationResource;
use App\Models\wbscte\Token;
use App\Models\wbscte\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DashboardNotificationController extends Controller
{
    //
    public function createNotification(Request $request){
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

                    if (in_array('create-notification', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'messages' => ['required'],
                            'start_date' => ['required'],
                            'end_date' => ['required']
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        try {
                            DB::beginTransaction();

                            $stDate = $request->start_date;
                            $endDate = $request->end_date;
                            $messages = $request->messages;

                            Notification::create([
                                'noti_message'  => $messages,
                                'noti_start_date' => $stDate,
                                'noti_end_date'  => $endDate,
                                'noti_active'    => 1,
                            ]);

                            $last_insert_id = DB::getPdo()->lastInsertId();
                            auditTrail($user_id, "Dashboard Notification with ID {$last_insert_id} Created.");
                            DB::commit();

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Notification Created Successfully',
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
    public function notificationList(Request $request){
        $now = date('Y-m-d');
        $dashboardNotificationList = Notification::where('noti_active', 1)->where('noti_start_date', '<=', $now)->where('noti_end_date', '>=', $now)->orderBy('noti_id', 'ASC')->get();

        if (sizeof($dashboardNotificationList) > 0) {
            $reponse = array(
                'error'                 =>  false,
                'message'               =>  'List of Notification found',
                'count'                 =>   sizeof($dashboardNotificationList),
                'notification_list' =>  NotificationResource::collection($dashboardNotificationList)
            );
            return response(json_encode($reponse), 200);
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'No Notification available'
            );
            return response(json_encode($reponse), 200);
        }


    }
}
