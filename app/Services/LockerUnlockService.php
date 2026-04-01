<?php

namespace App\Services;

use App\Models\LockerUnlockToken;
use Illuminate\Support\Facades\DB;
use App\Models\LockerUnlockJob;

class LockerUnlockService
{
    public function issue(array $data): LockerUnlockToken
    {
        return DB::transaction(function () use ($data) {

            // Create token
            $token = LockerUnlockToken::create([
                'locker_id' => $data['locker_id'],
                'reason' => $data['reason'],
                'authorized_by' => $data['authorized_by'] ?? 'SYSTEM',
                'issued_at' => now(),
                'expires_at' => now()->addMinutes(3),
                'rental_id' => $data['rental_id'] ?? null,
                'penalty_id' => $data['penalty_id'] ?? null,
                'admin_id' => $data['admin_id'] ?? null,
                'admin_card_id' => $data['admin_card_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
            ]);

            // Immediately create execution job
            LockerUnlockJob::create([
                'unlock_token_id' => $token->id,
                'locker_id' => $token->locker_id,
                'rental_id' => $token->rental_id,
                'status' => 'PENDING',
                'attempts' => 0,
                'max_attempts' => 3,
            ]);

            return $token;
        });
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