<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminSetting;
use App\Models\Locker;
use App\Services\LockerUnlockService;
use App\Models\LockerUnlockToken;
use App\Models\KioskDaemon;
use Illuminate\Support\Str;
use App\Services\KioskEventService;

class AdminSystemController extends Controller
{
    protected $events;

    public function __construct(KioskEventService $events)
    {
        $this->events = $events;
    }
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required'
        ]);

        $setting = AdminSetting::first();

        if (!$setting || !Hash::check($request->pin, $setting->emergency_pin)) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 403);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function emergencyUnlock()
    {
        $existing = LockerUnlockToken::where('reason', 'EMERGENCY_UNLOCK')
            ->whereHas('jobs', function ($q) {
                $q->whereIn('status', ['PENDING', 'PROCESSING']);
            })->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Emergency unlock already in progress'
            ], 409);
        }

        $daemon = KioskDaemon::where('kiosk_id', 'KIOSK-01')->first();

        if (!$daemon || now()->diffInSeconds($daemon->last_seen_at) > 20) {
            return response()->json([
                'message' => 'System offline'
            ], 503);
        }

        try {
            $lockers = Locker::orderBy('locker_number')->get();

            $batchId = Str::uuid();

            $this->events->log(
                'EMERGENCY_UNLOCK_INITIATED',
                [
                    'batch_id' => $batchId,
                    'total_lockers' => $lockers->count(),
                    'kiosk_id' => 'KIOSK-01',
                ],
                'WARNING',
                'Emergency unlock initiated for all lockers'
            );

            foreach ($lockers as $locker) {
                \Log::info("Unlocking locker ID: " . $locker->id);

                app(LockerUnlockService::class)->issue([
                    'locker_id' => $locker->id,
                    'reason' => 'ADMIN_OVERRIDE',
                    'batch_id' => $batchId,
                ]);
            }

            return response()->json([
                'success' => true,
                'batch_id' => $batchId
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required',
            'new_pin' => 'required|min:4|max:6'
        ]);

        $setting = AdminSetting::first();

        if (!$setting || !Hash::check($request->current_pin, $setting->emergency_pin)) {
            return response()->json([
                'message' => 'Current PIN is incorrect'
            ], 403);
        }

        $setting->update([
            'emergency_pin' => Hash::make($request->new_pin)
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
