<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if ($admin->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Forbidden. SUPER_ADMIN access required.'
            ], 403);
        }

        return $next($request);
    }
}
