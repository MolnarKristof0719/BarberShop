<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'userId',
        'profilePicture',
        'introduction',
        'isActive',
    ];

    protected $casts = [
        'isActive' => 'boolean',
    ];

    // --- Relationships ---
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'barberId');
    }

    public function offDays()
    {
        return $this->hasMany(BarberOffDay::class, 'barberId');
    }

    public function referencePictures()
    {
        return $this->hasMany(ReferencePicture::class, 'barberId');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'barberId');
    }
}
