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
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function locker()
    {
        return $this->belongsTo(Locker::class);
    }

    public function penalty()
    {
        return $this->hasOne(Penalty::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
