<?php

namespace App\Mail;

use App\Exceptions\SendBookingException;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewBookingNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Reserva - Tour #' . $this->booking->tour_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.new-booking',
            with: [
                'booking' => $this->booking,
                'tour' => $this->booking->tour,
                'hotel' => $this->booking->hotel,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    public static function sendBookingNotification(Booking $booking): void
    {
        try {
            Mail::to($booking->customer_email)
                ->cc(config('mail.admin_address'))
                ->queue(new NewBookingNotification($booking));


        } catch (\Exception $e) {
            Log::error('Failed to send email for booking #' . $booking->id . ': ' . $e->getMessage());
            Throw new SendBookingException();
        }
    }
}
