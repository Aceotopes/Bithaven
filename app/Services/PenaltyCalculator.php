<?php

namespace App\Services;

use App\Models\Penalty;
use Carbon\Carbon;

class PenaltyCalculator
{
    public function calculate(Penalty $penalty): float
    {
        $startedAt = Carbon::parse($penalty->started_at);
        $minutes = $startedAt->diffInMinutes(now());

        $amount = 5; // base penalty on expiry

        // +5 every 30 minutes
        $amount += floor($minutes / 30) * 5;

        // +10 every full hour
        $amount += floor($minutes / 60) * 10;

        return $amount;
    }
}