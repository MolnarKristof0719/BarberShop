<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'barberId',
        'userId',
        'appointmentDate',
        'appointmentTime',
        'status',
        'cancelledBy',
    ];

    protected $casts = [
        'appointmentDate' => 'date',
    ];

    public function setStatusAttribute(string $value): void
    {
        // Keep DB enum compatibility while accepting "done" from API callers.
        $this->attributes['status'] = $value === 'done' ? 'completed' : $value;
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barberId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'appointment_services',
            'appointmentId',
            'serviceId'
        );
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class, 'appointmentId');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'appointmentId');
    }
}
