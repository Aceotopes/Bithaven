<?php

namespace App\Services;

use App\Models\LockerUnlockToken;
use Illuminate\Support\Facades\DB;

class LockerUnlockService
{
    public function issue(array $data): LockerUnlockToken
    {
        return LockerUnlockToken::create([
            'locker_id' => $data['locker_id'],
            'reason' => $data['reason'],
            'authorized_by' => $data['authorized_by'] ?? 'SYSTEM',
            'issued_at' => now(),
            'expires_at' => now()->addSeconds(30),

            'rental_id' => $data['rental_id'] ?? null,
            'penalty_id' => $data['penalty_id'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
        ]);
    }

    public function consume(LockerUnlockToken $token): void
    {
        if ($token->isConsumed() || $token->isExpired()) {
            throw new \RuntimeException('Unlock token invalid');
        }

        $token->update([
            'consumed_at' => now(),
        ]);
    }

    // public function unlock(LockerUnlockToken $token): void
    // {
    //     DB::transaction(function () use ($token) {
    //         if ($token->consumed_at) {
    //             abort(409, 'Unlock token already consumed');
    //         }

    //         if ($token->expires_at->isPast()) {
    //             abort(409, 'Unlock token expired');
    //         }

    //         //hardware unlock first
    //         app(LockerHardwareService::class)->unlock($token->locker_id);

    //         //then consume token
    //         $token->update([
    //             'consumed_at' => now(),
    //         ]);
    //     });
    // }
}