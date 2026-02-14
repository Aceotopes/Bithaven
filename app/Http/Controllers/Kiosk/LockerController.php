<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Rental;
use App\Models\LockerUnlockToken;
use Illuminate\Support\Facades\DB;
use App\Services\KioskEventService;

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

    // public function unlock(
    //     Locker $locker,
    //     // LockerUnlockService $unlockService,
    //     // LockerHardwareService $hardware,
    //     KioskEventService $events
    // ) {
    //     return DB::transaction(function () use ($locker, $events) {

    //         // Lock row to prevent double unlock
    //         $token = LockerUnlockToken::where('locker_id', $locker->id)
    //             ->whereNull('consumed_at')
    //             ->where('expires_at', '>', now())
    //             ->orderBy('issued_at')
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$token) {
    //             $events->log(
    //                 'LOCKER_UNLOCK_DENIED',
    //                 [
    //                     'locker_id' => $locker->id,
    //                 ],
    //                 'WARNING',
    //                 'Unlock request without valid token'
    //             );

    //             return response()->json([
    //                 'message' => 'No valid unlock token'
    //             ], 409);
    //         }

    //         // try {
    //         //     $hardware->unlock($locker->id);
    //         // } catch (\Throwable $e) {
    //         //     $events->log(
    //         //         'LOCKER_HARDWARE_FAILED',
    //         //         [
    //         //             'locker_id' => $locker->id,
    //         //             'error' => $e->getMessage(),
    //         //         ],
    //         //         'ERROR',
    //         //         'Hardware unlock failed'
    //         //     );

    //         //     throw $e;
    //         // }


    //         // // Physical unlock (can throw later if hardware fails)
    //         // $hardware->unlock($locker->id);

    //         // // Consume token AFTER hardware success
    //         // $unlockService->consume($token);

    //         // Audit log
    //         $events->log(
    //             'LOCKER_UNLOCK_AUTHORIZED',
    //             [
    //                 'kiosk_id' => "KIOSK-01", // hardcoded for now, can be dynamic if needed
    //                 'student_id' => $token->rental ? $token->rental->student_id : null,
    //                 'rental_id' => $token->rental_id,
    //                 'penalty_id' => $token->penalty_id,
    //                 'payment_id' => $token->payment_id,
    //                 'locker_id' => $locker->id,
    //                 'unlock_token_id' => $token->id,
    //                 'reason' => $token->reason,
    //             ],
    //             'INFO',
    //             'Unlock authorized. Awaiting daemon execution.'
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'reason' => $token->reason,
    //         ]);
    //     });
    // }

    public function authorize(Locker $locker)
    {
        return DB::transaction(function () use ($locker) {

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

            return response()->json([
                'success' => true,
                'token_id' => $token->id,
                'locker_id' => $locker->id,
                'reason' => $token->reason,
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

        $token->update([
            'consumed_at' => now(),
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function pending()
    {
        $tokens = LockerUnlockToken::whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->get(['id', 'locker_id', 'reason']);

        return response()->json([
            'tokens' => $tokens
        ]);
    }

}
