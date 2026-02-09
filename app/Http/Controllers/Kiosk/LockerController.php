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
use App\Services\KioskEventService;
use App\Services\LockerHardwareService;

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
        LockerUnlockService $unlockService,
        LockerHardwareService $hardware,
        KioskEventService $events
    ) {
        return DB::transaction(function () use ($locker, $unlockService, $hardware, $events) {

            // 🔒 Lock row to prevent double unlock
            $token = LockerUnlockToken::where('locker_id', $locker->id)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->orderBy('issued_at')
                ->lockForUpdate()
                ->first();

            try {
                $hardware->unlock($locker->id);
            } catch (\Throwable $e) {
                $events->log(
                    'LOCKER_HARDWARE_FAILED',
                    [
                        'locker_id' => $locker->id,
                        'error' => $e->getMessage(),
                    ],
                    'ERROR',
                    'Hardware unlock failed'
                );

                throw $e;
            }

            if (!$token) {
                $events->log(
                    'LOCKER_UNLOCK_FAILED',
                    [
                        'locker_id' => $locker->id,
                    ],
                    'WARNING',
                    'No valid unlock token found'
                );

                return response()->json([
                    'message' => 'No valid unlock token'
                ], 409);
            }

            // Physical unlock (can throw later if hardware fails)
            $hardware->unlock($locker->id);

            // Consume token AFTER hardware success
            $unlockService->consume($token);

            // Audit log
            $events->log(
                'LOCKER_UNLOCKED',
                [
                    'kiosk_id' => "KIOSK-01", // hardcoded for now, can be dynamic if needed
                    'student_id' => $token->rental ? $token->rental->student_id : null,
                    'rental_id' => $token->rental_id,
                    'penalty_id' => $token->penalty_id,
                    'payment_id' => $token->payment_id,
                    'locker_id' => $locker->id,
                    'unlock_token_id' => $token->id,
                    'reason' => $token->reason,
                ],
                'INFO',
                'Locker unlocked successfully'
            );

            return response()->json([
                'success' => true,
                'reason' => $token->reason,
            ]);
        });
    }

}
