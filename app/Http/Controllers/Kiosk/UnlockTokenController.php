<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\LockerUnlockToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnlockTokenController extends Controller
{
    public function pending()
    {


        return DB::transaction(function () {

            $tokens = LockerUnlockToken::whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->get();

            return response()->json([
                'tokens' => $tokens
            ]);
        });
    }

    public function confirm(LockerUnlockToken $token)
    {
        if ($token->consumed_at !== null) {
            return response()->json([
                'message' => 'Token already consumed'
            ], 409);
        }

        if ($token->expires_at <= now()) {
            return response()->json([
                'message' => 'Token expired'
            ], 410);
        }

        $token->update([
            'consumed_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
