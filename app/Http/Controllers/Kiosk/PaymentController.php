<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Rental;
use App\Models\Locker;
use Illuminate\Support\Facades\DB;
use App\Services\PenaltyCalculator;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payPenalty(Request $request)
    {
        $request->validate([
            'penalty_id' => 'required|exists:penalties,id',
            'method' => 'required|in:CASH,ADMIN',
        ]);

        $penalty = Penalty::with('rental')->findOrFail($request->penalty_id);

        if ($penalty->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Penalty already settled'
            ], 422);
        }

        if ($penalty->frozen_at === null || $penalty->frozen_amount === null) {
            return response()->json([
                'message' => 'Penalty must be frozen before payment'
            ], 409);
        }

        DB::transaction(function () use ($penalty, $request) {

            //Create IMMUTABLE payment record
            Payment::create([
                'student_id' => $penalty->rental->student_id,
                'penalty_id' => $penalty->id,
                'amount' => $penalty->frozen_amount, // 🔒 frozen, never recalculated
                'method' => $request->input('method'),
                'status' => 'COMPLETED',
                'paid_at' => now(),
            ]);

            $penalty->update([
                'status' => 'PAID',
                'settled_at' => now(),
            ]);

            $penalty->rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'USER',
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function payRental(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'locker_number' => 'required|exists:lockers,locker_number',
            'duration_hours' => 'required|integer|min:1|max:12',
            'method' => 'required|in:CASH,ADMIN',
        ]);

        return DB::transaction(function () use ($request) {
            // Prevent multiple rentals
            $hasRental = Rental::where('student_id', $request->student_id)
                ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                ->exists();

            if ($hasRental) {
                abort(409, 'STUDENT_ALREADY_HAS_RENTAL');
            }

            // Lock locker
            $locker = Locker::where('locker_number', $request->locker_number)->firstOrFail();

            $lockerInUse = Rental::where('locker_id', $locker->id)
                ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                ->exists();

            if ($lockerInUse) {
                abort(409, 'LOCKER_UNAVAILABLE');
            }

            // Create rental
            $start = now();
            // $end = $start->copy()->addHours($request->duration_hours);
            $end = $start->copy()->addSeconds(50); // testing only

            $rental = Rental::create([
                'student_id' => $request->student_id,
                'locker_id' => $locker->id,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'ACTIVE',
            ]);

            // Create IMMUTABLE payment record
            Payment::create([
                'student_id' => $request->student_id,
                'rental_id' => $rental->id,
                'amount' => $request->duration_hours * 5, // fixed pricing
                'method' => $request->input('method'),
                'status' => 'COMPLETED',
                'paid_at' => now(),
            ]);

            return response()->json([
                'rental' => [
                    'id' => $rental->id,
                    'locker_number' => $locker->locker_number,
                    'start_time' => $start->toIso8601String(),
                    'end_time' => $end->toIso8601String(),
                    'status' => $rental->status,
                ],
            ]);
        });
    }
}
