<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'level',
        'message',
        'payload',
        'kiosk_id',

        'student_id',
        'rental_id',
        'penalty_id',
        'payment_id',
        'locker_id',
        'unlock_token_id',

        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
