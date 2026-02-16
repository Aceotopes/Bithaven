<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCard extends Model
{
    protected $filable = [
        'card_label',
        'rfid_uid',
        'status',
        'assigned_to',
        'assigned_at',
    ];
}
