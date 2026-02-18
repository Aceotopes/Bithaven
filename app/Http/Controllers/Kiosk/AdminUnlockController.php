<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AdminCard;
use App\Models\Locker;
use App\Services\LockerUnlockService;
use App\Services\KioskEventService;

class AdminUnlockController extends Controller
{
    public function forceUnlock(
        Request $request,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ) {
        $request->validate([
            'rfid_uid' => 'required|string',
            'locker_number' => 'required|integer',
            'kiosk_id' => 'required|string',
        ]);

        $uid = trim($request->rfid_uid);

        $card = AdminCard::where('rfid_uid', $uid)->first();

        if (!$card || $card->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'INVALID_ADMIN_CARD'
            ], 403);
        }

        $locker = Locker::where('locker_number', $request->locker_number)->first();

        if (!$locker) {
            return response()->json([
                'error' => 'LOCKER_NOT_FOUND'
            ], 404);
        }

        DB::transaction(function () use ($locker, $card, $unlockService, $events, $request) {

            // Issue unlock token
            $unlockService->issue([
                'locker_id' => $locker->id,
                'reason' => 'ADMIN_OVERRIDE',
                'authorized_by' => 'ADMIN',
                'admin_card_id' => $card->id,
            ]);

            $events->log(
                'ADMIN_FORCE_UNLOCK',
                [
                    'locker_id' => $locker->id,
                    'admin_card_id' => $card->id,
                    'card_label' => $card->card_label,
                ],
                'WARNING',
                'Locker force unlocked by admin'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Unlock job issued'
        ]);
    }
}
