<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\KioskEvent;
use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Locker;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;
use App\Services\LockerUnlockService;
use App\Services\PenaltyCalculator;
use App\Services\KioskEventService;


class RentalController extends Controller
{
    // public function start(Request $request)
    // {
    //     $request->validate([
    //         'student_id' => 'required|exists:students,id',
    //         'locker_number' => 'required|exists:lockers,locker_number',
    //         'duration_hours' => 'required|integer|min:1|max:12',
    //     ]);

    //     // Block ongoing rentals
    //     $hasOngoingRental = Rental::where('student_id', $request->student_id)
    //         ->whereIn('status', ['ACTIVE', 'EXPIRED'])
    //         ->exists();

    //     if ($hasOngoingRental) {
    //         return response()->json(['error' => 'STUDENT_ALREADY_HAS_RENTAL'], 409);
    //     }

    //     $locker = Locker::where('locker_number', $request->locker_number)->firstOrFail();

    //     $lockerInUse = Rental::where('locker_id', $locker->id)
    //         ->whereIn('status', ['ACTIVE', 'EXPIRED'])
    //         ->exists();

    //     if ($lockerInUse) {
    //         return response()->json(['error' => 'LOCKER_UNAVAILABLE'], 409);
    //     }

    //     $start = now();
    //     $end = $start->copy()->addHours($request->duration_hours);

    //     $rental = Rental::create([
    //         'student_id' => $request->student_id,
    //         'locker_id' => $locker->id,
    //         'start_time' => $start,
    //         'end_time' => $end,
    //         'status' => 'ACTIVE',
    //     ]);

    //     return response()->json([
    //         'rental' => [
    //             'id' => $rental->id,
    //             'locker_number' => $locker->locker_number,
    //             'start_time' => $start->toIso8601String(),
    //             'end_time' => $end->toIso8601String(),
    //             'status' => 'ACTIVE',
    //         ]
    //     ]);
    // }

    // public function activate(Rental $rental)
    // {
    //     if ($rental->status !== 'PENDING') {
    //         return response()->json(['message' => 'Rental not activatable'], 400);
    //     }

    //     $start = now();
    //     // $end = $start->copy()->addHours($rental->duration_hours);
    //     $end = $start->copy()->addSeconds(50); //for testing purposes only  

    //     $rental->update([
    //         'status' => 'ACTIVE',
    //         'start_time' => $start,
    //         'end_time' => $end,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'rental' => [
    //             'id' => $rental->id,
    //             'start_time' => $start->toIso8601String(),
    //             'end_time' => $end->toIso8601String(),
    //         ]
    //     ]);
    // }


    public function active(Request $request, KioskEventService $events)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $rental = Rental::where('student_id', $request->student_id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->with('locker')
            ->latest()
            ->first();

        if (!$rental) {
            return response()->json(['rental' => null]);
        }

        //Auto-expire if overdue
        if ($rental->status === 'ACTIVE' && $rental->end_time->isPast()) {
            $this->expireRental($rental, $events);
            $rental->refresh();
        }

        return response()->json([
            'rental' => [
                'id' => $rental->id,
                'locker_number' => $rental->locker->locker_number,
                'start_time' => $rental->start_time->toIso8601String(),
                'end_time' => $rental->end_time->toIso8601String(),
                'status' => $rental->status,
            ],
        ]);
    }

    // public function cancel(Rental $rental)
    // {
    //     if ($rental->status !== 'PENDING') {
    //         return response()->json(['message' => 'Cannot cancel'], 400);
    //     }

    //     $rental->update([
    //         'status' => 'CANCELLED',
    //         'ended_at' => now(),
    //         'ended_by' => 'USER',
    //     ]);

    //     return response()->json(['success' => true]);
    // }


    public function end(Rental $rental, LockerUnlockService $unlockService, KioskEventService $events)
    {
        if ($rental->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Rental is not Active'
            ], 400);
        }

        $rental->update([
            'status' => 'ENDED',
            'ended_at' => now(),
            'ended_by' => 'USER',
        ]);

        $unlockService->issue([
            'locker_id' => $rental->locker_id,
            'reason' => 'RENTAL_END',
            'rental_id' => $rental->id,
        ]);

        $events->log(
            'RENTAL_ENDED',
            [
                'kiosk_id' => 'KIOSK_01', // hardcoded for now
                'rental_id' => $rental->id,
                'locker_id' => $rental->locker_id,
                'student_id' => $rental->student_id,
            ],
            'INFO',
            'Rental ended by user'
        );

        return response()->json([
            'success' => true,
            'rental_id' => $rental->id,
        ]);
    }

    public function access($rentalId, LockerUnlockService $unlockService, KioskEventService $events)
    {
        $rental = Rental::findOrFail($rentalId);

        if ($rental->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Rental not active'
            ], 409);
        }

        $unlockService->issue([
            'locker_id' => $rental->locker_id,
            'reason' => 'RENTAL_ACCESS',
            'rental_id' => $rental->id,
        ]);

        $events->log(
            'LOCKER_ACCESSED',
            [
                'kiosk_id' => 'KIOSK_01',
                'rental_id' => $rental->id,
                'locker_id' => $rental->locker_id,
                'student_id' => $rental->student_id,
            ],
            'INFO',
            'Locker accessed without ending rental'
        );

        return response()->json([
            'message' => 'Access token issued'
        ]);
    }

    public function expire(Rental $rental, KioskEventService $events)
    {
        $this->expireRental($rental, $events);

        return response()->json([
            'success' => true,
            'rental_id' => $rental->id,
        ]);
    }

    private function expireRental(Rental $rental, KioskEventService $events): void
    {
        if ($rental->status !== 'ACTIVE') {
            return;
        }

        DB::transaction(function () use ($rental, $events) {

            // Mark rental expired
            $rental->update([
                'status' => 'EXPIRED',
            ]);

            // Create penalty ONCE (no amount)
            Penalty::firstOrCreate(
                ['rental_id' => $rental->id],
                [
                    'started_at' => $rental->end_time,
                    'status' => 'ACTIVE',
                ]
            );

            $events->log(
                'RENTAL_EXPIRED',
                [
                    'kiosk_id' => 'KIOSK_01', // hardcoded for now
                    'student_id' => $rental->student_id,
                    'rental_id' => $rental->id,
                    'payment_id' => $rental->payment_id,
                    'penalty_id' => $rental->penalty ? $rental->penalty->id : null,
                    'locker_id' => $rental->locker_id,
                ],
                'WARNING',
                'Rental expired automatically'
            );
        });
    }
}