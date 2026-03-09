<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function transactions(Request $request)
    {
        $query = PaymentSession::query()
            ->where('status', 'COMPLETED')
            ->with(['student', 'locker', 'penalty.rental.locker']);

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->type && $request->type !== 'ALL') {
            $query->where('context_type', $request->type);
        }
        if ($request->search) {

            $terms = explode(' ', $request->search);

            $query->whereHas('student', function ($q) use ($terms) {

                foreach ($terms as $term) {
                    $q->where(function ($sub) use ($term) {
                        $sub->where('first_name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%");
                    });
                }

            });
        }

        $sortField = $request->sort_field ?? 'created_at';
        $sortOrder = $request->sort_order == 1 ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortOrder);

        $sessions = $query
            ->paginate(20)
            ->through(function ($s) {

                $locker = null;
                $duration = null;

                if ($s->context_type === 'RENTAL') {
                    $locker = $s->locker?->locker_number;
                    $duration = $s->duration_hours
                        ? $s->duration_hours . ' hr'
                        : null;
                }

                if ($s->context_type === 'PENALTY') {

                    $penalty = $s->penalty;
                    $rental = $penalty?->rental;

                    $locker = $rental?->locker?->locker_number;

                    $duration = $penalty?->frozen_exceeded_duration;
                }

                return [
                    'id' => $s->id,
                    'student' => $s->student?->full_name,
                    'locker' => $locker,
                    'duration' => $duration,
                    'amount_paid' => $s->amount_paid,
                    'amount_due' => $s->amount_due,
                    'type' => $s->context_type,
                    'created_at' => $s->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json($sessions);
    }

    public function summary(Request $request)
    {
        $query = PaymentSession::query()
            ->where('status', 'COMPLETED');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $totalRevenue = (clone $query)->sum('amount_paid');

        $rentalRevenue = (clone $query)
            ->where('context_type', 'RENTAL')
            ->sum('amount_paid');

        $penaltyRevenue = (clone $query)
            ->where('context_type', 'PENALTY')
            ->sum('amount_paid');

        $transactions = (clone $query)->count();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'rental_revenue' => $rentalRevenue,
            'penalty_revenue' => $penaltyRevenue,
            'transactions' => $transactions,
        ]);
    }

    public function lockerRevenue()
    {
        $data = PaymentSession::query()
            ->where('status', 'COMPLETED')
            ->where('context_type', 'RENTAL')
            ->selectRaw('locker_id, COUNT(*) as rentals, SUM(amount_paid) as revenue')
            ->groupBy('locker_id')
            ->with('locker')
            ->get();

        return response()->json($data);
    }

    public function penalties()
    {
        $penalties = PaymentSession::query()
            ->where('status', 'COMPLETED')
            ->where('context_type', 'PENALTY')
            ->with(['student', 'locker'])
            ->latest()
            ->paginate(20);

        return response()->json($penalties);
    }
}