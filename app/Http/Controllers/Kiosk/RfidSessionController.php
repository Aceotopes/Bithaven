<?php
// FOR ADMIN SCAN
namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RfidScanSession;
use Illuminate\Support\Facades\DB;

class RfidSessionController extends Controller
{
    public function pending()
    {
        // Expire old sessions first
        RfidScanSession::where('status', 'PENDING')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'EXPIRED']);

        $session = RfidScanSession::where('kiosk_id', 'KIOSK-01')
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        return response()->json($session);
    }

    public function complete(Request $request, RfidScanSession $session)
    {
        $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        // Reload fresh from DB to prevent stale model issues
        $session = RfidScanSession::lockForUpdate()->find($session->id);

        if (!$session) {
            return response()->json(['error' => 'SESSION_NOT_FOUND'], 404);
        }

        if ($session->status !== 'PENDING') {
            return response()->json(['error' => 'INVALID_SESSION_STATE'], 422);
        }

        if ($session->expires_at <= now()) {
            $session->update(['status' => 'EXPIRED']);
            return response()->json(['error' => 'SESSION_EXPIRED'], 422);
        }

        DB::transaction(function () use ($session, $request) {
            $session->update([
                'status' => 'COMPLETED',
                'rfid_uid' => $request->rfid_uid,
            ]);
        });

        return response()->json(['success' => true]);
    }
}
