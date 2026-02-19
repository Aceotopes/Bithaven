<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locker extends Model
{
    protected $fillable = [
        'locker_number',
        'status',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental()
    {
        return $this->hasOne(Rental::class)
            ->whereIn('status', ['ACTIVE', 'EXPIRED'])
            ->latest();
    }
}
