<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RfidScanSession;

class RfidController extends Controller
{
    public function start(Request $request)
    {
        $admin = $request->user();

        // Expire old sessions first
        RfidScanSession::where('status', 'PENDING')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'EXPIRED']);

        // Check if another admin already has active session
        $existingSession = RfidScanSession::where('kiosk_id', 'KIOSK-01')
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingSession) {

            // If same admin, return that session instead of error
            if ($existingSession->admin_id === $admin->id) {
                return response()->json([
                    'scan_id' => $existingSession->id,
                    'status' => 'ALREADY_ACTIVE'
                ]);
            }

            // Another admin owns the scanner
            return response()->json([
                'error' => 'SCANNER_IN_USE'
            ], 409);
        }

        $session = RfidScanSession::create([
            'kiosk_id' => 'KIOSK-01',
            'admin_id' => $admin->id,
            'status' => 'PENDING',
            'expires_at' => now()->addSeconds(30),
        ]);

        return response()->json([
            'scan_id' => $session->id,
            'status' => 'CREATED'
        ]);
    }

    public function show(RfidScanSession $session, Request $request)
    {
        $admin = $request->user();

        if ($session->admin_id !== $admin->id) {
            return response()->json(['error' => 'FORBIDDEN'], 403);
        }

        // Auto-expire if needed
        if ($session->status === 'PENDING' && $session->expires_at <= now()) {
            $session->update(['status' => 'EXPIRED']);
        }

        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'rfid_uid' => $session->rfid_uid,
            'expires_at' => $session->expires_at,
        ]);
    }

    public function cancel(RfidScanSession $session, Request $request)
    {
        $admin = $request->user();

        if ($session->admin_id !== $admin->id) {
            return response()->json(['error' => 'FORBIDDEN'], 403);
        }

        if ($session->status !== 'PENDING') {
            return response()->json(['error' => 'INVALID_STATE'], 422);
        }

        $session->update([
            'status' => 'CANCELLED'
        ]);

        return response()->json(['success' => true]);
    }

}
