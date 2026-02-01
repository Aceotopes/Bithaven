<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Rental;
use Illuminate\Http\Request;

class LockerController extends Controller
{
    public function status()
    {
        // locker IDs that are currently occupied
        $occupiedLockerIds = Rental::whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->pluck('locker_id')
            ->toArray();

        $lockers = Locker::all()->map(function ($locker) use ($occupiedLockerIds) {
            return [
                'number' => $locker->locker_number,
                'status' => in_array($locker->id, $occupiedLockerIds)
                    ? 'OCCUPIED'
                    : 'AVAILABLE',
            ];
        });

        return response()->json([
            'lockers' => $lockers,
        ]);
    }
}
