<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Rental;
use App\Services\PenaltyCalculator;
use Illuminate\Http\Request;

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
}
