<?php

namespace App\Http\Controllers\kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KioskDaemon;

class DaemonController extends Controller
{
    public function heartbeat(Request $request)
    {
        $request->validate([
            'kiosk_id' => 'required|string'
        ]);

        KioskDaemon::updateOrCreate(
            ['kiosk_id' => $request->kiosk_id],
            ['last_seen_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}
