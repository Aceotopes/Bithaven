<?php
//TEMPORARY ONLY - This service simulates the hardware interaction for locking and unlocking lockers. In a real implementation, this would interface with the actual hardware API.  
namespace App\Services;

use Illuminate\Support\Facades\Log;

class LockerHardwareService
{
    public function unlock(int $lockerId): void
    {
        Log::info('[HARDWARE] Unlock locker', [
            'locker_id' => $lockerId,
        ]);
    }

    public function lock(int $lockerId): void
    {
        Log::info('[HARDWARE] Lock locker', [
            'locker_id' => $lockerId,
        ]);
    }
}
