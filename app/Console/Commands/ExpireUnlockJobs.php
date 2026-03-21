<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LockerUnlockJob;
use App\Services\KioskEventService;

class ExpireUnlockJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unlock:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unlock jobs with expired tokens';

    /**
     * Execute the console command.
     */
    public function handle(KioskEventService $events)
    {
        $jobs = LockerUnlockJob::with('token')
            ->where('status', 'PENDING')
            ->get();

        foreach ($jobs as $job) {

            if (!$job->token)
                continue;

            if ($job->token->isExpired()) {

                $job->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                ]);

                $events->log(
                    'UNLOCK_JOB_EXPIRED',
                    [
                        'locker_id' => $job->locker_id,
                        'unlock_token_id' => $job->token->id,
                    ],
                    'WARNING',
                    'Unlock job expired (scheduler)'
                );

                $this->info("Expired job ID: {$job->id}");
            }
        }

        return 0;
    }
}
