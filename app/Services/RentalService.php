<?php

// FOR ADMIN USE ONLY - END RENTAL EARLY
namespace App\Services;

use App\Models\Rental;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\DB;

class RentalService
{
    public function endEarly(
        int $rentalId,
        LockerUnlockService $unlockService,
        KioskEventService $events,
        int $adminCardId
    ): void {
        DB::transaction(function () use ($rentalId, $unlockService, $events, $adminCardId) {

            $rental = Rental::with('penalty')->findOrFail($rentalId);

            if ($rental->status !== 'ACTIVE') {
                throw new \RuntimeException('Rental is not active');
            }

            if ($rental->penalty && $rental->penalty->status === 'ACTIVE') {
                throw new \RuntimeException('Penalty must be cleared first.');
            }

            PaymentSession::where('locker_id', $rental->locker_id)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'CANCELLED']);

            $rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'ADMIN',
            ]);

            // Issue unlock token
            $unlockService->issue([
                'locker_id' => $rental->locker_id,
                'reason' => 'RENTAL_END',
                'rental_id' => $rental->id,
                'authorized_by' => 'ADMIN',
                'admin_card_id' => $adminCardId,
            ]);

            $events->log(
                'RENTAL_ENDED_ADMIN',
                [
                    'kiosk_id' => 'KIOSK-01', // hardcoded for now  
                    'rental_id' => $rental->id,
                    'locker_id' => $rental->locker_id,
                    // 'admin_card_id' => $adminCardId,
                ],
                'WARNING',
                'Rental ended early by admin override'
            );
        });
    }
}