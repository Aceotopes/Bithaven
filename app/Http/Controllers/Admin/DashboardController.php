<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Rental;
use App\Models\Penalty;
use App\Models\Locker;
use App\Models\PaymentSession;
use App\Models\Student;
use App\Models\KioskDaemon;
use App\Models\KioskEvent;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        // FIRST ROW SUMMARY KPI CARDS
        $activeRentals = Rental::where('status', 'ACTIVE')->count();

        $activePenalties = Penalty::where('status', 'ACTIVE')->count();

        $occupiedLockers = Rental::where('status', 'ACTIVE')
            ->distinct('locker_id')
            ->count('locker_id');

        $totalAvailableLockers = Locker::where('status', 'AVAILABLE')->count();

        $availableLockers = $totalAvailableLockers - $occupiedLockers;

        $outOfServiceLockers = Locker::where('status', 'OUT_OF_SERVICE')->count();

        $registeredStudents = Student::whereNotNull('rfid_uid')
            ->where('status', 'ACTIVE')
            ->count();

        $todayRevenue = PaymentSession::where('status', 'COMPLETED')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount_paid');

        $offlineKiosks = KioskDaemon::whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', now()->subMinutes(2))
            ->count();

        $recentEvents = KioskEvent::orderByDesc('created_at')
            ->limit(10)
            ->get([
                'id',
                'event_type',
                'level',
                'message',
                'created_at'
            ]);

        //SECOND ROW - REVENUE CHART
        $revenueLast7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            $rentalRevenue = PaymentSession::where('context_type', 'RENTAL')
                ->where('status', 'COMPLETED')
                ->whereDate('created_at', $date)
                ->sum('amount_paid');

            $penaltyRevenue = PaymentSession::where('context_type', 'PENALTY')
                ->where('status', 'COMPLETED')
                ->whereDate('created_at', $date)
                ->sum('amount_paid');

            return [
                'date' => $date->format('M d'),
                'rental' => $rentalRevenue,
                'penalty' => $penaltyRevenue,
                'total' => $rentalRevenue + $penaltyRevenue,
            ];
        });
        $revenueLabels = $revenueLast7Days->pluck('date');
        $rentalValues = $revenueLast7Days->pluck('rental');
        $penaltyValues = $revenueLast7Days->pluck('penalty');
        $totalValues = $revenueLast7Days->pluck('total');

        // SECOND ROW - RENTAL STATUS DISTRIBUTION
        $today = Carbon::today();
        $rentalStatusCounts = [
            Rental::where('status', 'ACTIVE')->count(),
            Rental::where('status', 'ENDED')->whereDate('ended_at', $today)->count(),
            Rental::where('status', 'EXPIRED')->whereDate('end_time', $today)->count(),
            PaymentSession::where('context_type', 'RENTAL')->where('status', 'CANCELLED')->whereDate('created_at', $today)->count(),
        ];

        // THIRD ROW - LOCKER UTILIZATION RATE
        $yesterday = Carbon::yesterday();
        $totalLockers = Locker::count();
        $utilizationRate = $totalAvailableLockers > 0
            ? round(($occupiedLockers / $totalAvailableLockers) * 100, 1)
            : 0;

        // THIRD ROW - RENTAL VELOCITY (TODAY'S RENTALS)
        $todayRentals = Rental::whereDate('created_at', $today)->count();
        $yesterdayRentals = Rental::whereDate('created_at', $yesterday)->count();

        $velocityChange = $yesterdayRentals > 0
            ? round((($todayRentals - $yesterdayRentals) / $yesterdayRentals) * 100, 1)
            : ($todayRentals > 0 ? 100 : 0);

        // ROW 3 - DEMAND VELOCITY TREND (Last 7 Days)
        $velocityLast7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'date' => $date->format('M d'),
                'count' => Rental::whereDate('created_at', $date)->count(),
            ];
        });

        $velocityLabels = $velocityLast7Days->pluck('date');
        $velocityValues = $velocityLast7Days->pluck('count');

        return response()->json([
            //FIRST ROW - KPI CARDS
            'active_rentals' => $activeRentals,
            'active_penalties' => $activePenalties,
            'available_lockers' => $availableLockers,
            'occupied_lockers' => $occupiedLockers,
            'out_of_service_lockers' => $outOfServiceLockers,
            'registered_students' => $registeredStudents,
            'today_revenue' => $todayRevenue,
            'offline_kiosks' => $offlineKiosks,
            'recent_events' => $recentEvents,

            //SECOND ROW - REVENUE CHART and RENTAL STATUS DISTRIBUTION
            'revenue_labels' => $revenueLabels,
            'rental_revenue_values' => $rentalValues,
            'penalty_revenue_values' => $penaltyValues,
            'total_revenue_values' => $totalValues,
            'rental_status_counts' => $rentalStatusCounts,

            // THIRD ROW - LOCKER UTILIZATION AND RENTAL VELOCITY
            'locker_utilization_rate' => $utilizationRate,
            'total_lockers' => $totalLockers,

            'today_rental_velocity' => $todayRentals,
            'yesterday_rental_velocity' => $yesterdayRentals,
            'velocity_change_percentage' => $velocityChange,
            'velocity_last7_labels' => $velocityLabels,
            'velocity_last7_values' => $velocityValues,
        ]);
    }
}