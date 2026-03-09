<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Rental;
use App\Models\LockerUnlockToken;
use App\Models\LockerUnlockJob;
use App\Models\KioskEvent;
use App\Models\Penalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveOperationsController extends Controller
{
    //
    public function lockers()
    {
        $lockers = Locker::orderBy('locker_number')->get();

        $result = $lockers->map(function ($locker) {

            /**
             * Get latest rental that is still relevant for monitoring.
             * We only care about ACTIVE or EXPIRED rentals.
             */
            $latestRental = Rental::with(['student', 'penalty'])
                ->where('locker_id', $locker->id)
                ->whereIn('status', ['ACTIVE', 'EXPIRED'])
                ->latest()
                ->first();

            /**
             * Derive operational state.
             * We DO NOT trust locker.status for occupancy.
             */
            $derivedStatus = match (true) {

                // Hardware state always wins
                $locker->status === 'OUT_OF_SERVICE'
                => 'OUT_OF_SERVICE',

                // Expired rental + active penalty = OVERDUE
                $latestRental &&
                $latestRental->status === 'EXPIRED' &&
                $latestRental->penalty &&
                $latestRental->penalty->status === 'ACTIVE'
                => 'OVERDUE',

                // Active rental = OCCUPIED
                $latestRental &&
                $latestRental->status === 'ACTIVE'
                => 'OCCUPIED',

                // Otherwise available
                default
                => 'AVAILABLE',
            };

            return [
                'id' => $locker->id,
                'locker_number' => $locker->locker_number,
                'status' => $derivedStatus,

                'rental' => $latestRental ? [
                    'id' => $latestRental->id,
                    'student_id' => $latestRental->student->id ?? null,

                    'first_name' => $latestRental->student->first_name ?? null,
                    'last_name' => $latestRental->student->last_name ?? null,
                    'year_level' => $latestRental->student->year_level ?? null,
                    'photo_url' => $latestRental->student->photo_url
                        ? asset('storage/' . $latestRental->student->photo_url)
                        : null,

                    'start_time' => $latestRental->start_time,
                    'end_time' => $latestRental->end_time,
                    'status' => $latestRental->status,
                ] : null,

                'penalty' => $latestRental && $latestRental->penalty ? [
                    'id' => $latestRental->penalty->id,
                    'status' => $latestRental->penalty->status,
                    'frozen_amount' => $latestRental->penalty->frozen_amount ?? null,
                ] : null,
            ];
        });

        return response()->json($result);
    }


    //ADMIN ACTIONS 
    public function forceUnlock(Locker $locker, Request $request)
    {
        return DB::transaction(function () use ($locker, $request) {

            // Prevent unlocking out-of-service locker
            if ($locker->status === 'OUT_OF_SERVICE') {
                return response()->json([
                    'message' => 'Locker is out of service'
                ], 422);
            }

            $admin = $request->user();

            // Create unlock token (authoritative record)
            $token = LockerUnlockToken::create([
                'locker_id' => $locker->id,
                'reason' => 'ADMIN_OVERRIDE',
                'authorized_by' => 'ADMIN',
                'issued_at' => now(),
                'admin_id' => $admin->id,
            ]);

            // Create unlock job for daemon
            LockerUnlockJob::create([
                'unlock_token_id' => $token->id,
                'locker_id' => $locker->id,
                'status' => 'PENDING',
            ]);

            // Log system event
            KioskEvent::create([
                'event_type' => 'ADMIN_FORCE_UNLOCK',
                'level' => 'WARNING',
                'message' => "Admin {$admin->username} forced unlock locker {$locker->locker_number}",
                'locker_id' => $locker->id,
                'unlock_token_id' => $token->id,
            ]);

            return response()->json([
                'success' => true,
                'token_id' => $token->id
            ]);
        });
    }

    public function endRental(Rental $rental, Request $request)
    {
        return DB::transaction(function () use ($rental, $request) {
            // Validate rental state
            if ($rental->status !== 'ACTIVE') {
                return response()->json([
                    'message' => 'Rental is not active'
                ], 422);
            }

            $admin = $request->user();

            // End rental
            $rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'ADMIN',
            ]);

            // Create unlock token
            $token = LockerUnlockToken::create([
                'locker_id' => $rental->locker_id,
                'reason' => 'RENTAL_END',
                'authorized_by' => 'ADMIN',
                'issued_at' => now(),
                'expires_at' => now()->addSeconds(30),
                'rental_id' => $rental->id,
                'admin_id' => $admin->id,
            ]);

            // Create unlock job
            LockerUnlockJob::create([
                'unlock_token_id' => $token->id,
                'locker_id' => $rental->locker_id,
                'status' => 'PENDING',
            ]);

            // Log event
            KioskEvent::create([
                'event_type' => 'RENTAL_ENDED_BY_ADMIN',
                'level' => 'WARNING',
                'message' => "Admin {$admin->username} ended rental early (Rental ID {$rental->id})",
                'rental_id' => $rental->id,
                'locker_id' => $rental->locker_id,
                'unlock_token_id' => $token->id,
            ]);

            return response()->json([
                'success' => true
            ]);
        });
    }

    public function clearPenalty(Penalty $penalty, Request $request)
    {
        return DB::transaction(function () use ($penalty, $request) {

            // Validate state
            if ($penalty->status !== 'ACTIVE') {
                return response()->json([
                    'message' => 'Penalty is not active'
                ], 422);
            }

            $admin = $request->user();
            $rental = $penalty->rental;

            if (!$rental) {
                return response()->json([
                    'message' => 'Associated rental not found'
                ], 422);
            }

            // Mark penalty as paid
            $penalty->update([
                'status' => 'PAID',
                'settled_at' => now(),
            ]);

            // End rental
            $rental->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => 'ADMIN',
            ]);

            // Create unlock token
            $token = LockerUnlockToken::create([
                'locker_id' => $rental->locker_id,
                'reason' => 'PENALTY_SETTLED',
                'authorized_by' => 'ADMIN',
                'issued_at' => now(),
                'expires_at' => now()->addSeconds(30),
                'penalty_id' => $penalty->id,
                'admin_id' => $admin->id,
            ]);

            // Create unlock job
            LockerUnlockJob::create([
                'unlock_token_id' => $token->id,
                'locker_id' => $rental->locker_id,
                'status' => 'PENDING',
            ]);

            // Log event
            KioskEvent::create([
                'event_type' => 'PENALTY_CLEARED_BY_ADMIN',
                'level' => 'WARNING',
                'message' => "Admin {$admin->username} cleared penalty ID {$penalty->id}",
                'penalty_id' => $penalty->id,
                'rental_id' => $rental->id,
                'locker_id' => $rental->locker_id,
                'unlock_token_id' => $token->id,
            ]);

            return response()->json([
                'success' => true
            ]);
        });
    }

    public function disableLocker(Locker $locker, Request $request)
    {
        return DB::transaction(function () use ($locker, $request) {

            // Check for active rental or unresolved penalty
            $engaged = Rental::where('locker_id', $locker->id)
                ->where(function ($query) {
                    $query->where('status', 'ACTIVE')
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('status', 'EXPIRED')
                                ->whereHas('penalty', function ($penaltyQuery) {
                                    $penaltyQuery->where('status', 'ACTIVE');
                                });
                        });
                })
                ->exists();

            if ($engaged) {
                return response()->json([
                    'message' => 'Cannot disable locker with active rental or unresolved penalty'
                ], 422);
            }

            if ($locker->status === 'OUT_OF_SERVICE') {
                return response()->json([
                    'message' => 'Locker already disabled'
                ], 422);
            }

            $locker->update([
                'status' => 'OUT_OF_SERVICE'
            ]);

            KioskEvent::create([
                'event_type' => 'LOCKER_DISABLED_BY_ADMIN',
                'level' => 'WARNING',
                'message' => "Admin {$request->user()->username} disabled locker {$locker->locker_number}",
                'locker_id' => $locker->id,
            ]);

            return response()->json(['success' => true]);
        });
    }
    public function enableLocker(Locker $locker, Request $request)
    {
        return DB::transaction(function () use ($locker, $request) {

            if ($locker->status === 'AVAILABLE') {
                return response()->json([
                    'message' => 'Locker already enabled'
                ], 422);
            }

            $locker->update([
                'status' => 'AVAILABLE'
            ]);

            KioskEvent::create([
                'event_type' => 'LOCKER_ENABLED_BY_ADMIN',
                'level' => 'INFO',
                'message' => "Admin {$request->user()->username} enabled locker {$locker->locker_number}",
                'locker_id' => $locker->id,
            ]);

            return response()->json(['success' => true]);
        });
    }
}