<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Rental;
use Illuminate\Http\Request;

class LiveOperationsController extends Controller
{
    public function lockers()
    {
        $lockers = Locker::orderBy('locker_number')->get();

        $result = $lockers->map(function ($locker) {

            /**
             * Get latest rental that is still relevant for monitoring.
             * We only care about ACTIVE or EXPIRED rentals.
             */
            $latestRental = Rental::with(['student', 'penalty'])
                ->where('locker_id', $locker->id)
                ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                ->latest()
                ->first();

            /**
             * Derive operational state.
             * We DO NOT trust locker.status for occupancy.
             */
            $derivedStatus = match (true) {

                // Hardware state always wins
                $locker->status === 'OUT_OF_SERVICE'
                => 'OUT_OF_SERVICE',

                // Expired rental + active penalty = OVERDUE
                $latestRental &&
                $latestRental->status === 'EXPIRED' &&
                $latestRental->penalty &&
                $latestRental->penalty->status === 'ACTIVE'
                => 'OVERDUE',

                // Active rental = OCCUPIED
                $latestRental &&
                $latestRental->status === 'ACTIVE'
                => 'OCCUPIED',

                // Otherwise available
                default
                => 'AVAILABLE',
            };

            return [
                'id' => $locker->id,
                'locker_number' => $locker->locker_number,
                'status' => $derivedStatus,

                'rental' => $latestRental ? [
                    'id' => $latestRental->id,
                    'student_id' => $latestRental->student->id ?? null,
                    'student_name' =>
                        $latestRental->student->first_name . ' ' .
                        $latestRental->student->last_name,
                    'start_time' => $latestRental->start_time,
                    'end_time' => $latestRental->end_time,
                    'status' => $latestRental->status,
                ] : null,

                'penalty' => $latestRental && $latestRental->penalty ? [
                    'id' => $latestRental->penalty->id,
                    'status' => $latestRental->penalty->status,
                    'frozen_amount' => $latestRental->penalty->frozen_amount ?? null,
                ] : null,
            ];
        });

        return response()->json($result);
    }
}