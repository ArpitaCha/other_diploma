<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $notif =  DB::table('wbscte_other_diploma_maintenance_noti')->where('is_active', 1)->first();

        if ($notif) {
            return response()->json([
                'error' => true,
                'message' => $notif->message,
            ], 503);
        } else {
            return $next($request);
        }
    }
}
