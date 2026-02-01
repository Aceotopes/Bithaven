<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $fillable = [
        'rental_id',
        'started_at',
        'resolved_at',
        'amount',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    // Relationships
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
