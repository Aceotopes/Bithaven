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
        'duration_hours',
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
        'duration_hours' => 'integer',
    ];

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_ENDED = 'ENDED';
    public const STATUS_CANCELLED = 'CANCELLED';

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

    public function unlockJobs()
    {
        return $this->hasMany(LockerUnlockJob::class);
    }

    public function activate()
    {
        if ($this->status !== self::STATUS_PENDING)
            return false;

        $start = now();

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'start_time' => $start,
            'end_time' => $start->copy()->addHours($this->duration_hours),
            // 'end_time' => $start->copy()->addSeconds(20),
        ]);

        return true;
    }

    public function cancel()
    {
        if ($this->status !== self::STATUS_PENDING)
            return false;

        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        return true;
    }

    public function endByUser()
    {
        if ($this->status !== self::STATUS_ACTIVE)
            return false;

        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
            'ended_by' => 'USER',
        ]);

        return true;
    }
}
