<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LockerUnlockJob;
use App\Services\KioskEventService;
use Illuminate\Support\Facades\DB;
use App\Models\Rental;

class UnlockJobController extends Controller
{

    protected $events;
    public function __construct(KioskEventService $events)
    {
        $this->events = $events;
    }

    // public function pending()
    // {
    //     $jobs = LockerUnlockJob::where('status', 'PENDING')
    //         ->whereHas('token', function ($q) {
    //             $q->whereNull('consumed_at')
    //                 ->where('expires_at', '>', now());
    //         })
    //         ->with('token')
    //         ->orderBy('created_at')
    //         ->get();
    //     \Log::info("Expired unlock jobs skipped: " . $jobs);

    //     return response()->json([
    //         'jobs' => $jobs
    //     ]);
    // }

    public function pending()
    {
        $jobs = LockerUnlockJob::where('status', 'PENDING')
            ->with('token')
            ->orderBy('id', 'asc')
            ->get();

        // dd(
        //     get_class($jobs->first()),
        //     $jobs->first()
        // );

        $validJobs = [];

        foreach ($jobs as $job) {

            if (!$job->token || $job->token->isExpired()) {

                LockerUnlockJob::where('id', $job->id)->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                    'attempts' => $job->attempts + 1,
                ]);

                $this->events->log(
                    'UNLOCK_JOB_EXPIRED',
                    [
                        'locker_id' => $job->locker_id,
                        'unlock_token_id' => optional($job->token)->id,
                    ],
                    'WARNING',
                    'Unlock job expired before execution'
                );

                continue;
            }

            $validJobs[] = $job;
        }
        // $nextJob = collect($validJobs)->first();
        return response()->json([
            'jobs' => $validJobs
        ]);
    }


    public function processing(LockerUnlockJob $job)
    {
        if ($job->status !== 'PENDING') {
            return response()->json(['message' => 'Invalid state'], 409);
        }

        if ($job->token->isExpired()) {
            $this->events->log(
                'UNLOCK_JOB_REJECTED',
                [
                    'locker_id' => $job->token->locker_id,
                    'unlock_token_id' => $job->token->id,
                ],
                'ERROR',
                'Unlock rejected: token expired'
            );

            return response()->json([
                'message' => 'Unlock token expired'
            ], 410);
        }

        if ($job->token->isConsumed()) {
            $this->events->log(
                'UNLOCK_JOB_REJECTED',
                [
                    'locker_id' => $job->token->locker_id,
                    'unlock_token_id' => $job->token->id,
                ],
                'ERROR',
                'Unlock rejected: token already used'
            );
            return response()->json([
                'message' => 'Token already consumed'
            ], 409);
        }

        $job->update([
            'status' => 'PROCESSING',
            'last_attempt_at' => now(),
        ]);

        $this->events->log(
            'UNLOCK_JOB_PROCESSING',
            [
                'locker_id' => $job->token->locker_id,
                'unlock_token_id' => $job->token->id,
            ],
            'INFO',
            'Unlock job processing started'
        );

        return response()->json(['success' => true]);
    }

    public function updateStatus(LockerUnlockJob $job, Request $request)
    {
        $request->validate([
            'status' => 'required|in:SUCCEEDED,FAILED'
        ]);

        // BLOCK FINALIZED JOBS FIRST
        if (in_array($job->status, ['SUCCEEDED', 'FAILED', 'CANCELLED'])) {
            return response()->json([
                'message' => 'Job already finalized'
            ], 409);
        }

        //  PROMOTE PENDING → PROCESSING
        if ($job->status === 'PENDING') {
            $job->update(['status' => 'PROCESSING']);
            $job->refresh(); // 🔥 REQUIRED
        }

        //  ENSURE CORRECT STATE
        if ($job->status !== 'PROCESSING') {
            return response()->json([
                'message' => 'Job not in processing state'
            ], 409);
        }

        if ($request->status === 'SUCCEEDED') {

            DB::transaction(function () use ($job) {

                $job->refresh();

                $job->update([
                    'status' => 'SUCCEEDED',
                    'succeeded_at' => now(),
                ]);

                // consume token
                if ($job->token && !$job->token->consumed_at) {
                    $job->token->update([
                        'consumed_at' => now()
                    ]);
                }

                $token = $job->token;

                $rental = $token && $token->rental_id
                    ? Rental::find($token->rental_id)
                    : null;

                if ($rental && $rental->status === Rental::STATUS_PENDING) {
                    $rental->activate();
                }
            });

            $this->events->log(
                'UNLOCK_JOB_SUCCEEDED',
                [
                    'locker_id' => $job->locker_id,
                    'unlock_token_id' => optional($job->token)->id,
                    'rental_id' => $job->rental_id,
                ],
                'INFO',
                'Locker successfully unlocked'
            );

            $batchId = optional($job->token)->batch_id;

            if ($batchId) {

                $remaining = LockerUnlockJob::whereHas('token', function ($q) use ($batchId) {
                    $q->where('batch_id', $batchId);
                })
                    ->whereIn('status', ['PENDING', 'PROCESSING'])
                    ->count();

                if ($remaining === 0) {

                    $this->events->log(
                        'EMERGENCY_UNLOCK_COMPLETED',
                        [
                            'batch_id' => $batchId,
                        ],
                        'INFO',
                        'Emergency unlock completed successfully'
                    );
                }
            }

        } else {

            $attempts = $job->attempts + 1;

            if ($attempts >= $job->max_attempts) {

                DB::transaction(function () use ($job, $attempts) {

                    $job->update([
                        'status' => 'FAILED',
                        'failed_at' => now(),
                        'attempts' => $attempts,
                    ]);

                    $token = $job->token;

                    $rental = $token && $token->rental_id
                        ? Rental::find($token->rental_id)
                        : null;

                    if ($rental && $rental->status === Rental::STATUS_PENDING) {
                        $rental->cancel();
                    }
                });

                $this->events->log(
                    'UNLOCK_JOB_FAILED',
                    [
                        'locker_id' => $job->locker_id,
                        'unlock_token_id' => optional($job->token)->id,
                    ],
                    'ERROR',
                    'Unlock job failed after max attempts'
                );

                $batchId = optional($job->token)->batch_id;

                if ($batchId) {
                    $this->events->log(
                        'EMERGENCY_UNLOCK_FAILED',
                        [
                            'batch_id' => $batchId,
                            'locker_id' => $job->locker_id,
                        ],
                        'ERROR',
                        'Emergency unlock encountered a failure'
                    );
                }

            } else {

                $job->update([
                    'status' => 'PENDING',
                    'attempts' => $attempts,
                    'last_attempt_at' => now(),
                ]);

                $this->events->log(
                    'UNLOCK_JOB_RETRY',
                    [
                        'locker_id' => $job->locker_id,
                        'unlock_token_id' => optional($job->token)->id,
                        'attempts' => $attempts,
                    ],
                    'WARNING',
                    'Unlock job retry attempt'
                );
            }
        }

        return response()->json(['success' => true]);
    }

    public function show(LockerUnlockJob $job)
    {
        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'attempts' => $job->attempts,
            'max_attempts' => $job->max_attempts,
        ]);
    }

    public function cancel(LockerUnlockJob $job)
    {
        if (in_array($job->status, ['SUCCEEDED', 'FAILED', 'CANCELLED'])) {
            return response()->json([
                'message' => 'Job already finalized'
            ], 409);
        }

        $job->update([
            'status' => 'CANCELLED'
        ]);

        $this->events->log(
            'UNLOCK_JOB_CANCELLED',
            [
                'locker_id' => $job->locker_id,
                'unlock_token_id' => optional($job->token)->id,
                'rental_id' => $job->rental_id,
                'attempts' => $job->attempts,
            ],
            'WARNING',
            'Polling timeout (Daemon Offline)'
        );

        return response()->json([
            'success' => true
        ]);
    }
}
