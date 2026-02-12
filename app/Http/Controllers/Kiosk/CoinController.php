<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\DB;
use App\Services\KioskEventService;
use App\Services\LockerUnlockService;

class CoinController extends Controller
{
    // public function insert(Request $request)
    // {
    //     $request->validate([
    //         'kiosk_id' => 'required|string',
    //         'value' => 'required|integer|in:1,5,10',
    //     ]);

    //     $session = PaymentSession::where('kiosk_id', $request->kiosk_id)
    //         ->where('status', 'ACTIVE')
    //         ->first();

    //     if (!$session) {
    //         return response()->json([
    //             'message' => 'No active payment session',
    //         ], 409);
    //     }

    //     DB::transaction(function () use ($session, $request) {
    //         $session->amount_paid += $request->value;

    //         // 🔑 PAYMENT COMPLETION RULE
    //         if ($session->amount_paid >= $session->amount_due) {
    //             $session->status = 'COMPLETED';
    //         }

    //         $session->save();
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'session' => $session->fresh(),
    //     ]);
    // }

    public function insert(
        Request $request,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ) {
        $request->validate([
            'kiosk_id' => 'required|string',
            'value' => 'required|integer|in:1,5,10',
        ]);

        return DB::transaction(function () use ($request, $unlockService, $events) {

            $session = PaymentSession::where('kiosk_id', $request->kiosk_id)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'No active payment session',
                ], 409);
            }

            // Prevent accepting coins after completion
            if ($session->status !== 'ACTIVE') {
                return response()->json([
                    'message' => 'Payment session already completed',
                ], 409);
            }

            $session->amount_paid += $request->value;

            $events->log(
                'COIN_INSERTED',
                [
                    'kiosk_id' => $request->kiosk_id,
                    'payment_session_id' => $session->id,
                    'amount' => $request->value,
                    'locker_id' => $session->locker_id,
                ],
                'INFO',
                'Coin inserted'
            );

            $paymentComplete = false;
            $lockerId = null;

            if ($session->amount_paid >= $session->amount_due) {

                $session->status = 'COMPLETED';
                $paymentComplete = true;
                $lockerId = $session->locker_id;

                if ($lockerId) {
                    $unlockService->issue([
                        'locker_id' => $lockerId,
                        'reason' => 'RENTAL_START',
                    ]);
                }

                $events->log(
                    'PAYMENT_SESSION_COMPLETED',
                    [
                        'payment_session_id' => $session->id,
                        'locker_id' => $lockerId,
                    ],
                    'INFO',
                    'Payment session completed via coins'
                );
            }

            $session->save();

            return response()->json([
                'success' => true,
                'session' => $session->fresh(),
                'amount_paid' => $session->amount_paid,
                'amount_due' => $session->amount_due,
                'remaining' => max(0, $session->amount_due - $session->amount_paid),
                'payment_complete' => $paymentComplete,
                'locker_id' => $lockerId,
            ]);
        });
    }

}
