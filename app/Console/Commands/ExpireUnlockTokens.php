<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LockerUnlockToken;
use App\Models\LockerUnlockJob;
use App\Services\KioskEventService;


class ExpireUnlockTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unused unlock tokens and related jobs';

    /**
     * Execute the console command.
     */
    public function handle(KioskEventService $events)
    {
        $tokens = LockerUnlockToken::whereNull('consumed_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($tokens as $token) {

            // LOG TOKEN EXPIRATION
            $events->log(
                'UNLOCK_TOKEN_EXPIRED',
                [
                    'locker_id' => $token->locker_id,
                    'unlock_token_id' => $token->id,
                    'admin_id' => $token->admin_id,
                    'admin_card_id' => $token->admin_card_id,
                ],
                'WARNING',
                'Unlock token expired (scheduler)'
            );

            // FAIL RELATED JOB
            LockerUnlockJob::where('unlock_token_id', $token->id)
                ->whereIn('status', ['PENDING', 'PROCESSING'])
                ->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                ]);
        }

        $this->info("Expired tokens processed: " . $tokens->count());
    }
}
