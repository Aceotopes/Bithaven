<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AdminCard;
use App\Models\Penalty;
use App\Services\PaymentService;
use App\Services\LockerUnlockService;
use App\Services\KioskEventService;

class AdminPenaltyController extends Controller
{
    public function clear(
        Request $request,
        PaymentService $paymentService,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ) {
        $request->validate([
            'rfid_uid' => 'required|string',
            'penalty_id' => 'required|exists:penalties,id',
            'kiosk_id' => 'required|string',
        ]);

        $card = AdminCard::where('rfid_uid', trim($request->rfid_uid))->first();

        if (!$card || $card->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'INVALID_ADMIN_CARD'
            ], 403);
        }

        $penalty = Penalty::with('rental')->findOrFail($request->penalty_id);

        if ($penalty->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'PENALTY_ALREADY_SETTLED'
            ], 409);
        }

        DB::transaction(function () use ($penalty, $card, $paymentService, $unlockService, $events) {

            // Finalize penalty as ADMIN
            $paymentService->finalizePenaltyDirect(
                $penalty->id,
                'ADMIN',
                $unlockService,
                $events,
                'ADMIN',
                $card->id
            );

            $events->log(
                'ADMIN_CLEARED_PENALTY',
                [
                    'penalty_id' => $penalty->id,
                    'rental_id' => $penalty->rental_id,
                    'admin_card_id' => $card->id,
                    'card_label' => $card->card_label,
                ],
                'WARNING',
                'Penalty cleared by admin override'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Penalty cleared by admin'
        ]);
    }
}
