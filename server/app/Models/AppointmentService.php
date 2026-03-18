<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'appointmentId',
        'serviceId',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointmentId');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }
}
