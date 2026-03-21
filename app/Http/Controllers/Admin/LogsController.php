<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KioskEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Activity Logs (Main Table)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = KioskEvent::query()
            ->with([
                'student',
                'locker',
                'rental',
                'penalty',
                'adminCard'
            ]);

        if ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->type && $request->type !== 'ALL') {

            switch ($request->type) {

                case 'SYSTEM':
                    $query->whereIn('event_type', [
                        'RENTAL_PAID',
                        'RENTAL_EXPIRED',
                        'PENALTY_PAID',
                        'LOCKER_UNLOCK_AUTHORIZED',
                        'PAYMENT_SESSION_COMPLETED'
                    ]);
                    break;

                case 'ADMIN':
                    $query->whereIn('event_type', [
                        'ADMIN_FORCE_UNLOCK',
                        'RENTAL_ENDED_BY_ADMIN',
                        'PENALTY_CLEARED_BY_ADMIN',
                        'LOCKER_DISABLED_BY_ADMIN',
                        'LOCKER_ENABLED_BY_ADMIN'
                    ]);
                    break;

                case 'SECURITY':
                    $query->whereIn('event_type', [
                        'ADMIN_LOGIN',
                        'ADMIN_LOGOUT',
                        'ADMIN_CARD_SCANNED',
                        'INVALID_ADMIN_CARD'
                    ]);
                    break;

                case 'PAYMENT':
                    $query->whereIn('event_type', [
                        'RENTAL_PAID',
                        'PENALTY_PAID',
                        'PAYMENT_SESSION_COMPLETED'
                    ]);
                    break;
            }
        }

        $perPage = $request->per_page ?? 15;
        $logs = $query
            ->latest()
            ->paginate($perPage)
            ->through(function ($event) {

                $timestamp = Carbon::parse($event->created_at)
                    ->format('F j, g:ia');

                $actor = 'System';

                if ($event->student) {

                    $actor = $event->student->first_name . ' ' . $event->student->last_name;

                } elseif ($event->adminCard) {

                    if ($event->adminCard->assigned_to) {
                        $actor = $event->adminCard->assigned_to;
                    } else {
                        $actor = $event->adminCard->card_label;
                    }
                }

                $target = null;

                if ($event->locker) {
                    $target = "Locker " . $event->locker->locker_number;
                } elseif ($event->rental) {
                    $target = "Rental #" . $event->rental->id;
                } elseif ($event->penalty) {
                    $target = "Penalty #" . $event->penalty->id;
                } elseif ($event->payment_id) {
                    $target = "Payment #" . $event->payment_id;
                }

                return [
                    'timestamp' => $timestamp,
                    'actor' => $actor,
                    'event' => $event->event_type,
                    'target' => $target,
                    'level' => $event->level,
                    'description' => $event->message,
                ];
            });
        return response()->json([
            'logs' => $logs
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Recent System Events
    |--------------------------------------------------------------------------
    */

    public function events()
    {
        $events = KioskEvent::latest()
            ->limit(10)
            ->get()
            ->map(function ($event) {

                return [
                    'id' => $event->id,
                    'text' => Carbon::parse($event->created_at)->format('H:i') . " - " . $event->message
                ];
            });

        return response()->json($events);
    }


    /*
    |--------------------------------------------------------------------------
    | Security Logs
    |--------------------------------------------------------------------------
    */

    public function security()
    {
        $securityTypes = [
            'ADMIN_LOGIN',
            'ADMIN_LOGOUT',
            'ADMIN_CARD_SCANNED',
            'INVALID_ADMIN_CARD',
            'ADMIN_FORCE_UNLOCK'
        ];

        $logs = KioskEvent::whereIn('event_type', $securityTypes)
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($event) {

                return [
                    'timestamp' => Carbon::parse($event->created_at)->format('Y-m-d H:i:s'),

                    'user' =>
                        $event->admin_card_id
                        ? "Admin Card #{$event->admin_card_id}"
                        : "System",

                    'event' => $event->event_type,

                    'ip' => request()->ip(),
                ];
            });

        return response()->json($logs);
    }
}