<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskDaemon extends Model
{
    protected $fillable = [
        'kiosk_id',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];
}
