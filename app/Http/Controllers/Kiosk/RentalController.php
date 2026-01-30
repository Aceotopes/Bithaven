<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Locker;
use Carbon\Carbon;


class RentalController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'locker_number' => 'required|exists:lockers,locker_number',
            'duration_hours' => 'required|integer|min:1|max:12',
        ]);

        // 1️⃣ Prevent multiple active rentals per student
        $hasActiveRental = Rental::where('student_id', $request->student_id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->exists();

        if ($hasActiveRental) {
            return response()->json([
                'error' => 'STUDENT_ALREADY_HAS_ACTIVE_RENTAL'
            ], 409);
        }

        // 2️⃣ Get locker
        $locker = Locker::where('locker_number', $request->locker_number)->first();

        // 3️⃣ Ensure locker is not currently rented
        $lockerInUse = Rental::where('locker_id', $locker->id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->exists();

        if ($lockerInUse) {
            return response()->json([
                'error' => 'LOCKER_UNAVAILABLE'
            ], 409);
        }

        // 4️⃣ Create rental
        $start = Carbon::now();
        $end = $start->copy()->addHours($request->duration_hours);

        $rental = Rental::create([
            'student_id' => $request->student_id,
            'locker_id' => $locker->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'ACTIVE',
        ]);

        return response()->json([
            'rental' => [
                'id' => $rental->id,
                'locker_number' => $locker->locker_number,
                'start_time' => $rental->start_time->toIso8601String(),
                'end_time' => $rental->end_time->toIso8601String(),
                'status' => $rental->status,
            ]
        ]);
    }

    public function active(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $rental = Rental::where('student_id', $request->student_id)
            ->whereIn('status', ['ACTIVE'])
            ->with('locker')
            ->first();

        if (!$rental) {
            return response()->json([
                'rental' => null
            ]);
        }

        return response()->json([
            'rental' => [
                'id' => $rental->id,
                'locker_number' => $rental->locker->locker_number,
                'start_time' => $rental->start_time->toIso8601String(),
                'end_time' => $rental->end_time->toIso8601String(),
                'status' => $rental->status,
            ]
        ]);
    }

    public function end(Rental $rental)
    {
        if ($rental->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Rental is not Active'
            ], 400);
        }

        $rental->status = 'ENDED';
        $rental->ended_at = Carbon::now();
        $rental->save();

        return response()->json([
            'success' => true,
            'rental_id' => $rental->id,
        ]);
    }
}