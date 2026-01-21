<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['service'];

     public function appointments()
    {
        return $this->belongsToMany(
            Appointment::class,
            'appointment_services',
            'serviceId',
            'appointmentId'
        );
    }
}
