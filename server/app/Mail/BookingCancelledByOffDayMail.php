<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledByOffDayMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment->load([
            'user',
            'barber.user',
            'services',
        ]);
    }

    public function build()
    {
        return $this->subject('Idopont lemondva - barber szabadnap')
            ->view('emails.booking-cancelled-by-offday');
    }
}
