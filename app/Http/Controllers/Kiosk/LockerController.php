<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Rental;
use App\Models\LockerUnlockToken;
use App\Services\LockerUnlockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LockerController extends Controller
{
    public function status()
    {
        // locker IDs that are currently occupied
        $occupiedLockerIds = Rental::whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->pluck('locker_id')
            ->toArray();

        $lockers = Locker::all()->map(function ($locker) use ($occupiedLockerIds) {
            return [
                'number' => $locker->locker_number,
                'status' => in_array($locker->id, $occupiedLockerIds)
                    ? 'OCCUPIED'
                    : 'AVAILABLE',
            ];
        });

        return response()->json([
            'lockers' => $lockers,
        ]);
    }

    public function unlock(
        Locker $locker,
        LockerUnlockService $unlockService
    ) {
        $token = LockerUnlockToken::where('locker_id', $locker->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderBy('issued_at')
            ->lockForUpdate()
            ->first();

        if (!$token) {
            return response()->json([
                'message' => 'No valid unlock token'
            ], 409);
        }

        $unlockService->unlock($token);

        return response()->json([
            'success' => true,
            'reason' => $token->reason,
        ]);
    }

}
