<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminCard;
use App\Models\Admin;
use App\Models\KioskEvent;
use App\Services\RentalService;
use App\Services\LockerUnlockService;
use App\Services\KioskEventService;

class AdminScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string',
            'kiosk_id' => 'required|string',
        ]);

        $card = AdminCard::where('rfid_uid', $request->rfid_uid)->first();

        if (!$card) {
            return response()->json([
                'error' => 'ADMIN_CARD_NOT_FOUND'
            ], 404);
        }

        if ($card->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'ADMIN_CARD_DISABLED'
            ], 403);
        }

        // Log event
        KioskEvent::create([
            'event_type' => 'ADMIN_CARD_SCANNED',
            'level' => 'INFO',
            'message' => 'Admin card scanned at kiosk',
            'payload' => [
                'card_id' => $card->id,
                'card_label' => $card->card_label,
            ],
            'kiosk_id' => $request->kiosk_id,
        ]);

        return response()->json([
            'success' => true,
            'card' => [
                'id' => $card->id,
                'label' => $card->card_label,
                'assigned_to' => $card->assigned_to,
            ]
        ]);
    }

    public function endRentalEarly(Request $request, RentalService $rentalService, LockerUnlockService $unlockService, KioskEventService $eventService)
    {
        $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'admin_card_uid' => 'required|string'
        ]);

        $card = AdminCard::where('rfid_uid', $request->admin_card_uid)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return response()->json([
                'error' => 'ADMIN_CARD_NOT_FOUND'
            ], 404);
        }

        $rentalService->endEarly(
            $request->rental_id,
            $unlockService,
            $eventService,
            $card->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Rental ended successfully'
        ]);
    }
}
