<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Locker;
use PhpParser\Node\Expr\Cast;

class Rental extends Model
{
    protected $fillable = [
        'student_id',
        'locker_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }
}
