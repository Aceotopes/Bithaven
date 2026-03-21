<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\LockerUnlockToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\KioskEventService;

class UnlockTokenController extends Controller
{
    public function __construct(protected KioskEventService $events)
    {
    }
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

            $this->events->log(
                'UNLOCK_TOKEN_REJECTED',
                [
                    'locker_id' => $token->locker_id,
                    'unlock_token_id' => $token->id,
                ],
                'WARNING',
                'Token already consumed'
            );

            return response()->json([
                'message' => 'Token already consumed'
            ], 409);
        }

        if ($token->expires_at <= now()) {

            $this->events->log(
                'UNLOCK_TOKEN_EXPIRED',
                [
                    'locker_id' => $token->locker_id,
                    'unlock_token_id' => $token->id,
                ],
                'WARNING',
                'Attempt to use expired token'
            );

            return response()->json([
                'message' => 'Token expired'
            ], 410);
        }

        $token->update([
            'consumed_at' => now()
        ]);

        $this->events->log(
            'UNLOCK_TOKEN_CONSUMED',
            [
                'locker_id' => $token->locker_id,
                'unlock_token_id' => $token->id,
            ],
            'INFO',
            'Unlock token consumed'
        );

        return response()->json([
            'success' => true
        ]);
    }
}
