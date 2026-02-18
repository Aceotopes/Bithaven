<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Locker;
use App\Models\Penalty;
use App\Models\Rental;

class LockerUnlockToken extends Model
{
    protected $fillable = [
        'locker_id',
        'reason',
        'authorized_by',
        'issued_at',
        'consumed_at',
        'expires_at',
        'rental_id',
        'penalty_id',
        'admin_id',
        'admin_card_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'consumed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function penalty()
    {
        return $this->belongsTo(Penalty::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function adminCard()
    {
        return $this->belongsTo(AdminCard::class);
    }
}
