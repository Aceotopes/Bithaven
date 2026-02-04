<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\DB;

class CoinController extends Controller
{
    public function insert(Request $request)
    {
        $request->validate([
            'kiosk_id' => 'required|string',
            'value' => 'required|integer|in:1,5,10',
        ]);

        $session = PaymentSession::where('kiosk_id', $request->kiosk_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No active payment session',
            ], 409);
        }

        DB::transaction(function () use ($session, $request) {
            $session->increment('amount_paid', $request->value);
        });

        // Refresh model
        $session->refresh();

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }
}
