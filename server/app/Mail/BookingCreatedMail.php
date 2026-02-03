<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        // Betöltjük, amire az emailben szükség van
        $this->appointment = $appointment->load([
            'user',
            'barber.user',
            'services',
        ]);
    }

    public function build()
    {
        return $this->subject('Sikeres időpontfoglalás – Barber Shop 💈')
            ->view('emails.booking-created');
    }
}
