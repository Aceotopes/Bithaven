<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use App\Models\Locker;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\PenaltyCalculator;
use App\Services\KioskEventService;


class PaymentSessionController extends Controller
{
    public function start(Request $request, PenaltyCalculator $calculator, KioskEventService $events)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',

            'kiosk_id' => 'required|string',
            'context_type' => 'required|in:RENTAL,PENALTY',

            // RENTAL
            'locker_id' => 'required_if:context_type,RENTAL|exists:lockers,id',
            'duration_hours' => 'required_if:context_type,RENTAL|integer|min:1|max:12',

            // PENALTY
            'penalty_id' => 'required_if:context_type,PENALTY|exists:penalties,id',
        ]);

        return DB::transaction(function () use ($request, $calculator, $events) {

            PaymentSession::where('kiosk_id', $request->kiosk_id)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'CANCELLED']);

            $penaltySnapshot = null;

            // Calculate amount_due
            if ($request->context_type === 'RENTAL') {

                $pricePerHour = 5;
                $amountDue = $request->duration_hours * $pricePerHour;

            } else {

                //PENALTY PAYMENT (DERIVED-ON-READ)
                $penalty = Penalty::with('rental')->findOrFail($request->penalty_id);

                // source of truth for penalty amount (live if not frozen, frozen if frozen)
                $snapshot = $calculator->calculate($penalty->rental);

                // freeze
                $penalty->update([
                    'frozen_at' => now(),
                    'frozen_amount' => $snapshot['amount'],
                    'frozen_exceeded_duration' => $snapshot['exceeded_duration'],
                    'frozen_breakdown' => $snapshot['breakdown'],
                ]);

                $events->log(
                    'PENALTY_FROZEN',
                    [
                        'kiosk_id' => 'KIOSK_01', //hard coded
                        'student_id' => $penalty->rental->student_id,
                        'penalty_id' => $penalty->id,
                        'rental_id' => $penalty->rental_id,
                        'locker_id' => $penalty->rental->locker_id,
                        'amount' => $snapshot['amount'],
                    ],
                    'INFO',
                    'Penalty frozen for payment'
                );

                $amountDue = $snapshot['amount'];

                $penaltySnapshot = [
                    'amount' => $snapshot['amount'],
                    'breakdown' => $snapshot['breakdown'],
                    'exceeded_duration' => $snapshot['exceeded_duration'],
                ];
            }

            $session = PaymentSession::create([
                'student_id' => $request->student_id,
                'kiosk_id' => $request->kiosk_id,
                'context_type' => $request->context_type,

                'locker_id' => $request->locker_id ?? null,
                'penalty_id' => $request->penalty_id ?? null,

                'duration_hours' => $request->duration_hours ?? null,

                'amount_due' => $amountDue,
                'amount_paid' => 0,

                'status' => 'ACTIVE',
                'expires_at' => now()->addMinutes(5),
            ]);

            return response()->json([
                'success' => true,
                'session' => $session,
                'penalty_snapshot' => $penaltySnapshot,
            ]);
        });
    }
}
