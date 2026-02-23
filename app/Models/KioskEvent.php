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
        'admin_card_id',
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }
}
