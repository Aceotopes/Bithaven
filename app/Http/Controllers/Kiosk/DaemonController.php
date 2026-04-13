<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KioskDaemon;
use Carbon\Carbon;

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

    public function status()
    {
        $daemon = KioskDaemon::latest('last_seen_at')->first();

        if (!$daemon || !$daemon->last_seen_at) {
            return response()->json([
                'status' => 'OFFLINE',
                'seconds_ago' => null,
                'last_seen_human' => 'Never',
            ]);
        }

        $lastSeen = $daemon->last_seen_at;
        $now = now();

        $seconds = abs($now->diffInSeconds($lastSeen, false));

        if ($seconds <= 20) {
            $status = 'ONLINE';
        } elseif ($seconds <= 60) {
            $status = 'STALE';
        } else {
            $status = 'OFFLINE';
        }

        return response()->json([
            'status' => $status,
            'seconds_ago' => $seconds,
            'last_seen_at' => $lastSeen,
            'last_seen_human' => $lastSeen->diffForHumans(),
        ]);
    }
}
