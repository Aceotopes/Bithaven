<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Locker;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;
use App\Services\PenaltyCalculator;


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


    public function active(Request $request)
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
            $this->expireRental($rental);
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


    public function end(Rental $rental)
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

        return response()->json([
            'success' => true,
            'rental_id' => $rental->id,
        ]);
    }

    public function expire(Rental $rental)
    {
        $this->expireRental($rental);

        return response()->json([
            'success' => true,
            'rental_id' => $rental->id,
        ]);
    }

    private function expireRental(Rental $rental): void
    {
        if ($rental->status !== 'ACTIVE') {
            return;
        }

        DB::transaction(function () use ($rental) {

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
        });
    }
}