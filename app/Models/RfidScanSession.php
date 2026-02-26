<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfidScanSession extends Model
{
    protected $fillable = [
        'kiosk_id',
        'admin_id',
        'status',
        'rfid_uid',
        'expires_at',
    ];
}
