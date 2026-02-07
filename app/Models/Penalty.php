<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $fillable = [
        'rental_id',

        'started_at',
        'settled_at',
        'status',

        'frozen_at',
        'frozen_amount',
        'frozen_breakdown',
        'frozen_exceeded_duration',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'settled_at' => 'datetime',
        'frozen_at' => 'datetime',

        'frozen_breakdown' => 'array',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isFrozen(): bool
    {
        return $this->frozen_at !== null;
    }
}
