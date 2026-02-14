<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LockerUnlockJob;


class UnlockJobController extends Controller
{
    public function pending()
    {
        $jobs = LockerUnlockJob::where('status', 'PENDING')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'jobs' => $jobs
        ]);
    }


    public function processing(LockerUnlockJob $job)
    {
        if ($job->status !== 'PENDING') {
            return response()->json(['message' => 'Invalid state'], 409);
        }

        $job->update([
            'status' => 'PROCESSING',
            'last_attempt_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateStatus(LockerUnlockJob $job, Request $request)
    {
        $request->validate([
            'status' => 'required|in:SUCCEEDED,FAILED'
        ]);

        if ($job->status !== 'PROCESSING') {
            return response()->json([
                'message' => 'Job not in processing state'
            ], 409);
        }

        if ($request->status === 'SUCCEEDED') {

            $job->update([
                'status' => 'SUCCEEDED',
                'succeeded_at' => now(),
            ]);

            $job->token->update([
                'consumed_at' => now()
            ]);

        } else {

            $attempts = $job->attempts + 1;

            if ($attempts >= $job->max_attempts) {
                $job->update([
                    'status' => 'FAILED',
                    'failed_at' => now(),
                    'attempts' => $attempts,
                ]);
            } else {
                $job->update([
                    'status' => 'PENDING',
                    'attempts' => $attempts,
                    'last_attempt_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
