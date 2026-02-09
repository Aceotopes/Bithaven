<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Rental;
use App\Services\PenaltyCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\KioskEventService;


class PenaltyController extends Controller
{
    public function active(Request $request, PenaltyCalculator $calculator)
    {
        $studentId = $request->query('student_id');

        $rental = Rental::where('student_id', $studentId)
            ->where('status', 'EXPIRED')
            ->latest()
            ->first();

        if (!$rental) {
            return response()->json(['penalty' => null]);
        }

        $penalty = Penalty::where('rental_id', $rental->id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$penalty) {
            return response()->json(['penalty' => null]);
        }

        // Live if not frozen, frozen if frozen
        $snapshot = $calculator->calculate($rental, $penalty);

        return response()->json([
            'penalty' => [
                'id' => $penalty->id,
                'rental_id' => $penalty->rental_id,
                'started_at' => $penalty->started_at,
                'status' => $penalty->status,

                // frozen snapshot or live calculation
                'amount' => $snapshot['amount'],
                'breakdown' => $snapshot['breakdown'],
                'exceeded_duration' => $snapshot['exceeded_duration'],

                // !!! live vs frozen flag 
                'is_frozen' => (bool) $penalty->frozen_at,
            ]
        ]);
    }

    /**
     * Settle penalty (NO recalculation here)
     */
    public function settle(Penalty $penalty, KioskEventService $events)
    {
        if ($penalty->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Penalty already settled'
            ], 400);
        }

        // SAFETY CHECK — penalty MUST be frozen before settlement
        if (!$penalty->frozen_at || $penalty->frozen_amount === null) {
            return response()->json([
                'message' => 'Penalty must be frozen before settlement'
            ], 409);
        }

        DB::transaction(function () use ($penalty, $events) {

            // Mark penalty as PAID (NO recalculation)
            $penalty->update([
                'status' => 'PAID',
                'settled_at' => now(),
            ]);

            // End the rental
            $penalty->rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'USER',
            ]);

            $events->log(
                'PENALTY_SETTLED',
                [
                    'penalty_id' => $penalty->id,
                    'rental_id' => $penalty->rental_id,
                ],
                'INFO',
                'Penalty marked as paid'
            );
        });

        return response()->json([
            'success' => true,
        ]);
    }

    public function live(Penalty $penalty, PenaltyCalculator $calculator)
    {
        if ($penalty->status !== 'ACTIVE') {
            return response()->json([
                'amount' => 0,
                'exceeded_duration' => '00:00:00',
                'breakdown' => [],
            ]);
        }

        $snapshot = $calculator->calculate($penalty->rental);

        return response()->json([
            'amount' => $snapshot['amount'],
            'exceeded_duration' => $snapshot['exceeded_duration'],
            'breakdown' => $snapshot['breakdown'],
        ]);
    }
}
