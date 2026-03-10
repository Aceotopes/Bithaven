<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialController extends Controller
{
    public function transactions(Request $request)
    {
        $query = PaymentSession::query()
            ->where('status', 'COMPLETED')
            ->with(['student', 'locker', 'penalty.rental.locker']);

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
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
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
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

    public function revenueSummary(Request $request)
    {
        $query = PaymentSession::query()
            ->where('status', 'COMPLETED');

        /*
        |--------------------------------------
        | RANGE FILTER (7D / 30D / 3M / 1Y)
        |--------------------------------------
        */

        $days = null;

        if ($request->range) {

            switch ($request->range) {

                case '7D':
                    $days = 7;
                    break;

                case '30D':
                    $days = 30;
                    break;

                case '3M':
                    $days = 90;
                    break;

                case '1Y':
                    $days = 365;
                    break;
            }

            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        /*
        |--------------------------------------
        | MANUAL DATE FILTER
        |--------------------------------------
        */

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        /*
        |--------------------------------------
        | DAILY TREND
        |--------------------------------------
        */

        $daily = (clone $query)
            ->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as transactions,
            SUM(amount_paid) as revenue,
            SUM(CASE WHEN context_type="RENTAL" THEN amount_paid ELSE 0 END) as rental,
            SUM(CASE WHEN context_type="PENALTY" THEN amount_paid ELSE 0 END) as penalty
        ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /*
        |--------------------------------------
        | HOURLY REVENUE
        |--------------------------------------
        */

        $hourly = (clone $query)
            ->selectRaw('
            HOUR(created_at) as hour,
            COUNT(*) as transactions,
            SUM(amount_paid) as revenue
        ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        /*
        |--------------------------------------
        | TOTAL METRICS
        |--------------------------------------
        */

        $totalRevenue = (clone $query)->sum('amount_paid');
        $transactions = (clone $query)->count();

        /*
/*
|--------------------------------------
| REVENUE GROWTH (LAST DAY VS PREVIOUS DAY)
|--------------------------------------
*/

        /*
 |--------------------------------------------------------------------------
 | REVENUE GROWTH (ADAPTIVE PERIOD)
 |--------------------------------------------------------------------------
 */

        $growth = 0;

        $start = null;
        $end = now();

        /*
        | Determine current period
        */

        if ($request->start_date && $request->end_date) {

            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();

        } elseif ($days) {

            $start = now()->subDays($days);

        }

        /*
        | Calculate previous period
        */

        if ($start) {

            $periodLength = $start->diffInDays($end);

            $previousStart = (clone $start)->subDays($periodLength + 1);
            $previousEnd = (clone $start)->subDay();

            $currentRevenue = PaymentSession::query()
                ->where('status', 'COMPLETED')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount_paid');

            $previousRevenue = PaymentSession::query()
                ->where('status', 'COMPLETED')
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->sum('amount_paid');

            if ($previousRevenue > 0) {
                $growth = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
            }
        }

        return response()->json([
            'daily' => $daily,
            'hourly' => $hourly,
            'total_revenue' => $totalRevenue,
            'transactions' => $transactions,
            'growth' => round($growth ?? 0, 2)
        ]);
    }
}