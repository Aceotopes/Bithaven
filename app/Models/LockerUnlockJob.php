<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockerUnlockJob extends Model
{
    protected $fillable = [
        'unlock_token_id',
        'locker_id',
        'rental_id',
        'attempts',
        'max_attempts',
        'last_attempt_at',
        'succeeded_at',
        'failed_at',
        'status',
    ];

    public function token()
    {
        return $this->belongsTo(LockerUnlockToken::class, 'unlock_token_id');
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
