<?php
namespace App\Services;

use App\Models\KioskEvent;

class KioskEventService
{
    public function log(
        string $type,
        array $context = [],
        string $level = 'INFO',
        ?string $message = null
    ): void {
        KioskEvent::create([
            'event_type' => $type,
            'level' => $level,
            'message' => $message,
            'payload' => $context,
            'kiosk_id' => $context['kiosk_id'] ?? null,
            'student_id' => $context['student_id'] ?? null,
            'rental_id' => $context['rental_id'] ?? null,
            'penalty_id' => $context['penalty_id'] ?? null,
            'payment_id' => $context['payment_id'] ?? null,
            'locker_id' => $context['locker_id'] ?? null,
            'unlock_token_id' => $context['unlock_token_id'] ?? null,
            'created_at' => now(),
        ]);
    }
}