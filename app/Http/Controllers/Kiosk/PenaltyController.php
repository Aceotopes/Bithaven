<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Rental;
use App\Services\PenaltyCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

        return response()->json([
            'penalty' => [
                'id' => $penalty->id,
                'rental_id' => $penalty->rental_id,
                'started_at' => $penalty->started_at,
                'amount' => $calculator->calculate($penalty),
                'status' => $penalty->status,

            ]
        ]);
    }

    public function settle(Penalty $penalty, PenaltyCalculator $calculator)
    {
        if ($penalty->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Penalty already settled'
            ], 400);
        }

        DB::transaction(function () use ($penalty, $calculator) {
            $amount = $calculator->calculate($penalty);

            // 1. Mark penalty as PAID
            $penalty->update([
                'status' => 'PAID',
                'settled_at' => now(),
                'amount' => $amount,
            ]);

            // 2. End the rental
            $rental = $penalty->rental;

            $rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'USER',
            ]);
        });

        return response()->json([
            'success' => true,
        ]);
    }
}
