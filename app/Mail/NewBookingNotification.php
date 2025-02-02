<?php

namespace App\Mail;


use App\Models\Booking;
use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;


class NewBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $tour;
    public $hotel;

    public function __construct(Booking $booking)
{
    $this->booking = $booking;
    $this->tour = $booking->tour;
    $this->hotel = $booking->hotel;
}

    public function build()
{
    return $this->subject('Tour Booking Confirmation')
        ->view('emails.bookings.new-booking')
        ->with([
            'booking' => $this->booking,
            'tour' => $this->tour,
            'hotel' => $this->hotel
        ]);
}
}
