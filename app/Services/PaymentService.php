<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Rental;
use App\Models\Locker;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function finalizeFromSession(
        PaymentSession $session,
        string $method,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ): Payment {

        return DB::transaction(function () use ($session, $method, $unlockService, $events) {

            if ($session->status !== 'COMPLETED') {
                throw new \RuntimeException('Payment session not completed');
            }

            return match ($session->context_type) {
                'RENTAL' => $this->finalizeRentalFromSession(
                    $session,
                    $method,
                    $unlockService,
                    $events
                ),

                'PENALTY' => $this->finalizePenaltyFromSession(
                    $session,
                    $method,
                    $unlockService,
                    $events
                ),

                default => throw new \RuntimeException('Invalid payment session type'),
            };
        });
    }

    private function finalizeRentalFromSession(
        PaymentSession $session,
        string $method,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ): Payment {

        $locker = Locker::findOrFail($session->locker_id);

        // Prevent double rental
        $lockerInUse = Rental::where('locker_id', $locker->id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->exists();

        if ($lockerInUse) {
            throw new \RuntimeException('LOCKER_UNAVAILABLE');
        }

        $start = now();
        $end = $start->copy()->addHours($session->duration_hours);
        // $end = $start->copy()->addSeconds(10);

        if ($locker->status !== 'AVAILABLE') {
            abort(response()->json([
                'error' => 'LOCKER_OUT_OF_SERVICE',
                'locker_id' => $locker->id
            ], 409));
        }

        $rental = Rental::create([
            'student_id' => $session->student_id, // attach if needed
            'locker_id' => $locker->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'ACTIVE',
        ]);

        $payment = Payment::create([
            'student_id' => $session->student_id,
            'rental_id' => $rental->id,
            'amount' => $session->amount_due,
            'method' => $method,
            'status' => 'COMPLETED',
            'paid_at' => now(),
        ]);

        $unlockService->issue([
            'locker_id' => $locker->id,
            'reason' => 'RENTAL_START',
            'rental_id' => $rental->id,
        ]);

        $events->log(
            'RENTAL_PAID',
            [
                'student_id' => $session->student_id,
                'kiosk_id' => 'KIOSK-1', // hardcoded for now
                'payment_id' => $payment->id,
                'locker_id' => $locker->id,
                'rental_id' => $rental->id,
            ],
            'INFO',
            'Rental finalized from session'
        );
        return $payment;
    }

    private function finalizePenaltyFromSession(
        PaymentSession $session,
        string $method,
        LockerUnlockService $unlockService,
        KioskEventService $events,
        string $endedBy = 'USER',
        ?int $adminCardId = null
    ): Payment {
        $penalty = Penalty::with('rental')->findOrFail($session->penalty_id);

        if ($penalty->status !== 'ACTIVE') {
            throw new \RuntimeException('Penalty already settled');
        }

        $payment = Payment::create([
            'student_id' => $penalty->rental->student_id,
            'penalty_id' => $penalty->id,
            'amount' => $session->amount_due,
            'method' => $method,
            'status' => 'COMPLETED',
            'paid_at' => now(),
        ]);

        $penalty->update([
            'status' => 'PAID',
            'settled_at' => now(),
        ]);

        $penalty->rental->update([
            'status' => 'ENDED',
            'ended_at' => now(),
            'ended_by' => $endedBy,
        ]);

        $unlockService->issue([
            'locker_id' => $penalty->rental->locker_id,
            'reason' => 'PENALTY_SETTLED',
            'penalty_id' => $penalty->id,
            'authorized_by' => $endedBy === 'ADMIN' ? 'ADMIN' : 'SYSTEM',
            'admin_card_id' => $adminCardId,
        ]);

        $events->log(
            'PENALTY_PAID',
            [
                'student_id' => $penalty->rental->student_id,
                'rental_id' => $penalty->rental_id,
                'payment_id' => $payment->id,
                'locker_id' => $penalty->rental->locker_id,
                'penalty_id' => $penalty->id,
            ],
            'INFO',
            'Penalty finalized from session'
        );
        return $payment;
    }

    /**
     * Manual rental payment (ADMIN fallback)
     */
    public function finalizeRentalDirect(
        int $studentId,
        int $lockerNumber,
        int $durationHours,
        string $method,
        LockerUnlockService $unlockService,
        KioskEventService $events
    ): void {

        $locker = Locker::where('locker_number', $lockerNumber)->firstOrFail();

        $lockerInUse = Rental::where('locker_id', $locker->id)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->exists();

        if ($lockerInUse) {
            throw new \RuntimeException('LOCKER_UNAVAILABLE');
        }

        $start = now();
        $end = $start->copy()->addHours($durationHours);

        $rental = Rental::create([
            'student_id' => $studentId,
            'locker_id' => $locker->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'ACTIVE',
        ]);

        $payment = Payment::create([
            'student_id' => $studentId,
            'rental_id' => $rental->id,
            'amount' => $durationHours * 5,
            'method' => $method,
            'status' => 'COMPLETED',
            'paid_at' => now(),
        ]);

        $unlockService->issue([
            'locker_id' => $locker->id,
            'reason' => 'RENTAL_START',
            'rental_id' => $rental->id,
        ]);

        $events->log(
            'RENTAL_PAID',
            [
                'student_id' => $studentId,
                'rental_id' => $rental->id,
                'payment_id' => $payment->id,
                'locker_id' => $locker->id,
            ],
            'INFO',
            'Rental paid directly'
        );
    }

    /**
     * Manual penalty payment (ADMIN or fallback)
     */
    public function finalizePenaltyDirect(
        int $penaltyId,
        string $method,
        LockerUnlockService $unlockService,
        KioskEventService $events,
        string $endedBy = 'USER',
        ?int $adminCardId = null
    ): void {

        $penalty = Penalty::with('rental')->findOrFail($penaltyId);

        if ($penalty->status !== 'ACTIVE') {
            throw new \RuntimeException('Penalty already settled');
        }

        PaymentSession::where('penalty_id', $penaltyId)
            ->where('status', 'ACTIVE')
            ->update([
                'status' => 'CANCELLED'
            ]);

        $payment = Payment::create([
            'student_id' => $penalty->rental->student_id,
            'penalty_id' => $penalty->id,
            'amount' => $penalty->frozen_amount,
            'method' => $method,
            'status' => 'COMPLETED',
            'paid_at' => now(),
        ]);

        $penalty->update([
            'status' => 'PAID',
            'settled_at' => now(),
        ]);

        $penalty->rental->update([
            'status' => 'ENDED',
            'ended_at' => now(),
            'ended_by' => $endedBy,
        ]);

        $unlockService->issue([
            'locker_id' => $penalty->rental->locker_id,
            'reason' => 'PENALTY_SETTLED',
            'penalty_id' => $penalty->id,
            'authorized_by' => $endedBy === 'ADMIN' ? 'ADMIN' : 'SYSTEM',
            'admin_card_id' => $adminCardId,
        ]);

        $events->log(
            'PENALTY_PAID',
            [
                'penalty_id' => $penalty->id,
                'payment_id' => $payment->id,
            ],
            'INFO',
            'Penalty paid directly'
        );
    }
}
