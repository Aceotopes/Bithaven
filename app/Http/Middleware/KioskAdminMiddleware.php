<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AdminCard;

class KioskAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $uid = $request->header('X-Admin-UID');

        if (!$uid) {
            return response()->json([
                'error' => 'ADMIN_UID_REQUIRED'
            ], 401);
        }

        $card = AdminCard::where('rfid_uid', $uid)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return response()->json([
                'error' => 'INVALID_ADMIN_CARD'
            ], 403);
        }

        // Attach card to request for later use
        $request->attributes->set('admin_card', $card);

        return $next($request);
    }
}


