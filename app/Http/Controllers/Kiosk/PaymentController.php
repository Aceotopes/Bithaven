<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Penalty;
use App\Models\Rental;
use App\Services\PaymentService;
use App\Services\LockerUnlockService;
use App\Services\KioskEventService;

class PaymentController extends Controller
{
    /**
     * Manual penalty payment (ADMIN or fallback)
     */
    public function payPenalty(Request $request, PaymentService $paymentService, LockerUnlockService $unlockService, KioskEventService $events)
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

        if (!$penalty->frozen_at || $penalty->frozen_amount === null) {
            return response()->json([
                'message' => 'Penalty must be frozen before payment'
            ], 409);
        }

        DB::transaction(function () use ($penalty, $request, $paymentService, $unlockService, $events) {
            $paymentService->finalizePenaltyDirect(
                $request->penalty_id,
                $request->input('method'),
                $unlockService,
                $events
            );
        });

        return response()->json(['success' => true]);
    }

    /**
     * Manual rental payment (ADMIN fallback)
     */
    public function payRental(Request $request, PaymentService $paymentService, LockerUnlockService $unlockService, KioskEventService $events)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'locker_number' => 'required|exists:lockers,locker_number',
            'duration_hours' => 'required|integer|min:1|max:12',
            'method' => 'required|in:CASH,ADMIN',
        ]);

        DB::transaction(function () use ($request, $paymentService, $unlockService, $events) {
            $paymentService->finalizeRentalDirect(
                $request->student_id,
                $request->locker_number,
                $request->duration_hours,
                $request->input('method'),
                $unlockService,
                $events
            );
        });

        return response()->json(['success' => true]);
    }
}
