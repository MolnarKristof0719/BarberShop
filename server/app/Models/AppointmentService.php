<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentServiceFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'appointmentId',
        'serviceId',
    ];
}
