<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSession extends Model
{
    protected $fillable = [
        'kiosk_id',
        'context_type',
        'locker_id',
        'penalty_id',
        'duration_hours',
        'amount_due',
        'amount_paid',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'amount_due' => 'float',
        'amount_paid' => 'float',
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }

    public function penalty()
    {
        return $this->belongsTo(Penalty::class);
    }
}
