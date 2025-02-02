<?php

namespace Tests\Unit\booking;

use App\Events\BookingCreated;
use App\Listeners\SendBookingConfirmationEmail;
use App\Mail\NewBookingNotification;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewBookingEmailTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tour = Tour::factory()->create();
        $this->hotel = Hotel::factory()->create();
    }

    public function test_creates_a_booking_email_event()
    {
        Mail::fake();
        Event::fake();

        $bookingData = [
            'tour_id' => $this->tour->id,
            'hotel_id' => $this->hotel->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'number_of_people' => 2,
            'booking_date' => '2025-03-01',
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'tour_id',
                    'hotel_id',
                    'customer_name',
                    'customer_email',
                    'number_of_people',
                    'booking_date',
                ]
            ]);

        Event::assertDispatched(BookingCreated::class);

        Event::assertListening(
            BookingCreated::class,
            SendBookingConfirmationEmail::class
        );
    }

    public function test_sends_confirmation_email_when_booking_is_created()
    {
        Mail::fake();

        $booking = Booking::factory()->create();

        Event::dispatch(new BookingCreated($booking));

        Mail::assertSent(NewBookingNotification::class, function ($mail) use ($booking) {
            return $mail->booking->id === $booking->id;
        });
    }



    public function test_validates_required_fields()
    {
        $response = $this->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'tour_id',
                'hotel_id',
                'customer_name',
                'customer_email',
                'number_of_people',
                'booking_date'
            ]);
    }
}
