<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use App\Models\Locker;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PaymentSessionController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'kiosk_id' => 'required|string',
            'context_type' => 'required|in:RENTAL,PENALTY',

            // RENTAL
            'locker_id' => 'required_if:context_type,RENTAL|exists:lockers,id',
            'duration_hours' => 'required_if:context_type,RENTAL|integer|min:1|max:12',

            // PENALTY
            'penalty_id' => 'required_if:context_type,PENALTY|exists:penalties,id',
        ]);

        return DB::transaction(function () use ($request) {

            // 1️⃣ Cancel any existing ACTIVE session for this kiosk
            PaymentSession::where('kiosk_id', $request->kiosk_id)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'CANCELLED']);

            // 2️⃣ Calculate amount_due
            if ($request->context_type === 'RENTAL') {
                $pricePerHour = 5; // centralize later
                $amountDue = $request->duration_hours * $pricePerHour;
            } else {
                $penalty = Penalty::findOrFail($request->penalty_id);
                $amountDue = $penalty->amount;
            }

            // 3️⃣ Create payment session
            $session = PaymentSession::create([
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
            ]);
        });
    }
}
