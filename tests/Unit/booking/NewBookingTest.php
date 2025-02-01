<?php

namespace Tests\Unit\booking;

use App\Exceptions\SendBookingException;
use App\Mail\NewBookingNotification;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewBookingTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();


        Mail::fake();
    }

    public function test_new_booking_correct()
    {
        $tour= Tour::factory()->create();
        $hotel= Hotel::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 'Test User',
            'customer_email' => 'prueba@gmail.com',
            'number_of_people' => 2,
            'booking_date' => '2022-12-12',
        ]);
       $response->assertStatus(201)->assertJson(['message'=> 'Booking created successfully']);
    }

    public function test_new_booking_incorrect_without_data()
    {

        $response = $this->postJson('/api/bookings', []);
        $response->assertStatus(422)->assertJson(['message' => 'Validation error']);
    }


    public function test_new_booking_Validation_error()
    {
        $tour= Tour::factory()->create();
        $hotel= Hotel::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 1,
            'customer_email' => 'prueba@gmail.com',
            'number_of_people' => 2,
            'booking_date' => '2022-12-12',
        ]);
        $response->assertStatus(422)->assertJson(['message'=> 'Validation error']);

    }

    public function test_booking_email_is_sent()
    {

        $tour = Tour::factory()->create();
        $hotel = Hotel::factory()->create();


        $response = $this->postJson('/api/bookings', [
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'number_of_people' => 2,
            'booking_date' => '2024-02-15',
        ]);


        $response->assertStatus(201);
        Mail::assertQueued(NewBookingNotification::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_booking_email_not_sent()
    {
        Mail::fake();

        $tour = Tour::factory()->create();
        $hotel = Hotel::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 'Test User',
            'customer_email' => 'invalid-email@email.con',
            'number_of_people' => 2,
            'booking_date' => '2024-02-15',
        ]);

        $response->assertStatus(500);

        Mail::assertNothingSent();

        $this->assertDatabaseMissing('bookings', [
            'customer_email' => 'invalid-email'
        ]);
    }


}
