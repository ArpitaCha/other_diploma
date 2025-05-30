<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\wbscte\Token;
use App\Models\wbscte\User;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user_type = $request->user_type;

        if ($user_type) {
            return $next($request);
        }
        
        $token = $request->header('token');
        if (!$token) {
            return response()->json([
                'error' => true,
                'message' => 'Token is missing in request header'
            ], 401);
        }

        $now = now();
        $token_check = Token::where('t_token', $token)
                            ->where('t_expired_on', '>=', $now)
                            ->first();

        if (!$token_check) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        $user = User::select('u_id', 'u_ref', 'u_role_id','u_inst_id')
                    ->find($token_check->t_user_id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'User not found'
            ], 404);
        }

        $url_ids = DB::table('wbscte_other_diploma_auth_roles_permissions')
                    ->where('rp_role_id', $user->u_role_id)
                    ->pluck('rp_url_id');

        if ($url_ids->isEmpty()) {
            return response()->json([
                'error' => true,
                'message' => "Oops! You don't have sufficient permission"
            ], 403);
        }

        $allowed_urls = DB::table('wbscte_other_diploma_auth_urls')
                        ->where('url_visible', 1)
                        ->whereIn('url_id', $url_ids)
                        ->pluck('url_name')
                        ->toArray();
                        

        // Attach user and permissions to request
        $request->merge([
            'user_data'    => $user,
            'allowed_urls' => $allowed_urls,
        ]);

        return $next($request);
    }
}
