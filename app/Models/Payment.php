<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'student_id',
        'rental_id',
        'penalty_id',
        'amount',
        'method',
        'reference_code',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // Relationships

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function penalty()
    {
        return $this->belongsTo(Penalty::class);
    }
}
