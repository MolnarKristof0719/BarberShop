<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'appointmentId',
        'barberId',
        'userId',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // --- Relationships ---
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointmentId');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
