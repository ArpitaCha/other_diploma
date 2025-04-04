<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\wbscte\User;
use App\Models\wbscte\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class checkAuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token = Token::where('t_token', '=', $request->header('token'))->first();

            if ($token) {
                if ($token->t_expired_on < $now) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  'Token Expired'
                    ], 401);
                } else {
                    $user_id = $token->t_user_id;
                    $user_data = User::where('u_id', $user_id)->first();
                    $role_ids = DB::table('wbscte_other_diploma_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_ids) > 0) {
                        $urls = DB::table('wbscte_other_diploma_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_ids)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');

                        $request->request->add(['user_data' => $user_data, 'url_data' => $url_data]);
                        return $next($request);
                    } else {
                        $request->request->add(['user_data' => $user_data]);
                        return $next($request);
                    }
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Invalid Token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Token Required'
            ], 401);
        }
    }
}
