<?php

namespace App\Services;

use App\Models\Penalty;
use Carbon\Carbon;
use App\Models\Rental;

class PenaltyCalculator
{
    // public function calculate(Penalty $penalty): float
    // {
    //     $startedAt = Carbon::parse($penalty->started_at);
    //     $minutes = $startedAt->diffInMinutes(now());

    //     logger()->info('PenaltyCalculator::calculate', [
    //         'penalty_id' => $penalty->id,
    //         'started_at' => $startedAt->toDateTimeString(),
    //         'now' => now()->toDateTimeString(),
    //         'minutes' => $minutes,
    //     ]);

    //     $amount = 5; // base penalty on expiry

    //     // +5 every 30 minutes
    //     $amount += floor($minutes / 30) * 5;

    //     // +10 every full hour
    //     $amount += floor($minutes / 60) * 10;

    //     return $amount;
    // }

    // public function calculateWithSnapshot(Rental $rental): array
    // {
    //     $endTime = $rental->end_time;
    //     $now = now();

    //     $minutes = $endTime->diffInMinutes($now);

    //     $breakdown = [];

    //     // Base penalty
    //     $amount = 5;
    //     $breakdown[] = [
    //         'label' => 'Base penalty (rental expired)',
    //         'amount' => 5,
    //     ];

    //     // +5 every 30 minutes
    //     $halfHours = floor($minutes / 30);
    //     for ($i = 1; $i <= $halfHours; $i++) {
    //         $amount += 5;
    //         $breakdown[] = [
    //             'label' => "+30 minutes penalty",
    //             'amount' => 5,
    //         ];
    //     }

    //     // +10 every full hour
    //     $fullHours = floor($minutes / 60);
    //     for ($i = 1; $i <= $fullHours; $i++) {
    //         $amount += 10;
    //         $breakdown[] = [
    //             'label' => "+1 hour extended penalty",
    //             'amount' => 10,
    //         ];
    //     }

    //     // Human-readable exceeded duration
    //     $exceededDuration = $endTime->diffForHumans($now, true);

    //     return [
    //         'amount' => $amount,
    //         'exceeded_duration' => $exceededDuration,
    //         'breakdown' => $breakdown,
    //     ];
    // }

    // public function calculateWithSnapshot(Rental $rental): array
    // {
    //     $endTime = $rental->end_time;
    //     $now = now();

    //     $seconds = $endTime->diffInSeconds($now);
    //     $tiers = max(0, floor($seconds / 10)); // 10 sec per tier

    //     $amount = 5 + ($tiers * 5);

    //     $breakdown = [
    //         [
    //             'label' => 'Base penalty (expired)',
    //             'amount' => 5,
    //         ],
    //     ];

    //     for ($i = 1; $i <= $tiers; $i++) {
    //         $breakdown[] = [
    //             'label' => "+10 sec penalty",
    //             'amount' => 5,
    //         ];
    //     }

    //     return [
    //         'amount' => $amount,
    //         'exceeded_duration' => $endTime->diffForHumans($now, true),
    //         'breakdown' => $breakdown,
    //     ];
    // }

    public function calculate(Rental $rental, ?Penalty $penalty = null): array
    {
        //return frozen values
        if ($penalty && $penalty->frozen_at) {
            return [
                'amount' => $penalty->frozen_amount,
                'breakdown' => $penalty->breakdown ?? [],
                'exceeded_duration' =>
                    $penalty->started_at->diffForHumans($penalty->frozen_at, true),
            ];
        }

        //live calculation
        $now = now();
        $endTime = $rental->end_time;

        $seconds = max(0, $endTime->diffInSeconds($now));
        $tiers = intdiv($seconds, 10);

        $amount = 5 + ($tiers * 5);

        $breakdown = [
            ['label' => 'Base penalty (expired)', 'amount' => 5],
        ];

        for ($i = 0; $i < $tiers; $i++) {
            $breakdown[] = [
                'label' => '+10 sec penalty',
                'amount' => 5,
            ];
        }

        return [
            'amount' => $amount,
            'breakdown' => $breakdown,
            'exceeded_duration' => $endTime->diffForHumans($now, true),
        ];
    }
}

