<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Locker;
use App\Models\AdminCard;
use App\Models\Rental;
use App\Models\Payment;
use App\Models\Penalty;
use App\Services\KioskEventService;

class AdminLockerController extends Controller
{
    public function disable(
        Request $request,
        KioskEventService $events
    ) {
        $request->validate([
            'locker_number' => 'required|integer',
            'admin_card_uid' => 'required|string',
        ]);

        $card = AdminCard::where('rfid_uid', $request->admin_card_uid)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return response()->json(['error' => 'ADMIN_CARD_NOT_FOUND'], 404);
        }

        $locker = Locker::where('locker_number', $request->locker_number)->first();

        if (!$locker) {
            return response()->json(['error' => 'LOCKER_NOT_FOUND'], 404);
        }

        $hasActiveRental = Rental::where('locker_id', $locker->id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->exists();

        if ($hasActiveRental) {
            return response()->json([
                'error' => 'LOCKER_IN_USE'
            ], 400);
        }

        $locker->update([
            'status' => 'OUT_OF_SERVICE'
        ]);

        $events->log(
            'LOCKER_DISABLED',
            [
                'locker_id' => $locker->id,
                'admin_card_id' => $card->id,
            ],
            'WARNING',
            'Locker disabled for maintenance'
        );

        return response()->json([
            'success' => true,
            'message' => 'Locker disabled'
        ]);
    }

    public function enable(
        Request $request,
        KioskEventService $events
    ) {
        $request->validate([
            'locker_number' => 'required|integer',
            'admin_card_uid' => 'required|string',
        ]);

        $card = AdminCard::where('rfid_uid', $request->admin_card_uid)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return response()->json(['error' => 'ADMIN_CARD_NOT_FOUND'], 404);
        }

        $locker = Locker::where('locker_number', $request->locker_number)->first();

        if (!$locker) {
            return response()->json(['error' => 'LOCKER_NOT_FOUND'], 404);
        }

        $locker->update([
            'status' => 'AVAILABLE'
        ]);

        $events->log(
            'LOCKER_ENABLED',
            [
                'locker_id' => $locker->id,
                'admin_card_id' => $card->id,
            ],
            'INFO',
            'Locker enabled'
        );

        return response()->json([
            'success' => true,
            'message' => 'Locker enabled'
        ]);
    }

    public function index()
    {
        $lockers = Locker::with([
            'activeRental.penalty'
        ])
            ->orderBy('locker_number')
            ->get()
            ->map(function ($locker) {

                $rental = $locker->activeRental;
                $penalty = $rental?->penalty;

                if ($locker->status === 'OUT_OF_SERVICE') {
                    $state = 'OUT_OF_SERVICE';
                } elseif ($penalty && $penalty->status === 'ACTIVE') {
                    $state = 'PENALTY';
                } elseif ($rental && $rental->status === 'ACTIVE') {
                    $state = 'IN_USE';
                } else {
                    $state = 'AVAILABLE';
                }

                return [
                    'id' => $locker->id,
                    'locker_number' => $locker->locker_number,
                    'status' => $locker->status,
                    'operational_state' => $state,
                ];
            });

        return response()->json([
            'lockers' => $lockers
        ]);
    }

    public function show($lockerNumber)
    {
        $locker = Locker::where('locker_number', $lockerNumber)
            ->with([
                'activeRental.student',
                'activeRental.penalty',
                'activeRental.payments'
            ])
            ->firstOrFail();

        $rental = $locker->activeRental;

        if (!$rental) {
            return response()->json([
                'locker' => [
                    'number' => $locker->locker_number,
                    'status' => $locker->status,
                ],
                'rental' => null,
                'penalty' => null,
            ]);
        }

        $penalty = $rental->penalty;

        $latestPayment = $rental->payments()
            ->latest()
            ->first();

        return response()->json([
            'locker' => [
                'number' => $locker->locker_number,
                'status' => $locker->status,
            ],
            'rental' => [
                'id' => $rental->id,
                'status' => $rental->status,
                'start_time' => $rental->start_time,
                'end_time' => $rental->end_time,
                'time_remaining_seconds' => now()->diffInSeconds($rental->end_time, false),
                'student' => [
                    'id' => $rental->student->id,
                    'student_number' => $rental->student->student_number,
                    'name' => $rental->student->first_name . ' ' . $rental->student->last_name,
                ],
                'payment' => $latestPayment ? [
                    'amount' => $latestPayment->amount,
                    'method' => $latestPayment->method,
                    'paid_at' => $latestPayment->paid_at,
                ] : null,
            ],
            'penalty' => $penalty ? [
                'id' => $penalty->id,
                'status' => $penalty->status,
                'frozen_amount' => $penalty->frozen_amount,
                'exceeded_duration' => $penalty->frozen_exceeded_duration,
                'started_at' => $penalty->started_at,
            ] : null,
        ]);
    }
}
