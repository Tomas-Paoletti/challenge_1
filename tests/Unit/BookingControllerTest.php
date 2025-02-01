<?php

use App\Models\Booking;
use App\Models\Tour;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Benchmark;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can create a booking', function () {
    $tour = Tour::factory()->create();
    $hotel = Hotel::factory()->create();
    $bookingData = Booking::factory()->make([
        'tour_id' => $tour->id,
        'hotel_id' => $hotel->id,
    ])->toArray();

    $response = $this->postJson('/api/bookings', $bookingData);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment([
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => $bookingData['customer_name'],
            'customer_email' => $bookingData['customer_email'],
        ]);
});

it('fails to create a booking with invalid data', function () {
    $response = $this->postJson('/api/bookings', []);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['tour_id', 'hotel_id', 'customer_name', 'customer_email', 'number_of_people', 'booking_date']);
});

it('can retrieve all bookings', function () {
    Booking::factory()->count(3)->create();

    $response = $this->getJson('/api/bookings');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(3);
});

it('can retrieve all bookings performing ok', function(){
    Booking::factory()->count(1000)->create();

    $benchmark = Benchmark::measure([
        'normal' => fn () => $this->get('/api/bookings')
    ]);

    $this->assertTrue($benchmark['normal'] < 300);
})->repeat(10);


it('can retrieve bookings with filters', function () {
    $booking1 = Booking::factory()->create(['booking_date' => now()->subDays(2)]);
    $booking2 = Booking::factory()->create(['booking_date' => now()->subDays(1)]);
    $booking3 = Booking::factory()->create(['booking_date' => now()]);

    $response = $this->getJson('/api/bookings?start_date=' . now()->subDays(1)->toDateString());
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment(['id' => $booking2->id])
        ->assertJsonFragment(['id' => $booking3->id])
        ->assertJsonMissing(['id' => $booking1->id]);
});

it('can retrieve a single booking', function () {
    $booking = Booking::factory()->create();

    $response = $this->getJson("/api/bookings/{$booking->id}");
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'id' => $booking->id,
            'tour_id' => $booking->tour_id,
            'hotel_id' => $booking->hotel_id,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
        ]);
});

it('returns 404 for a non-existent booking', function () {
    $response = $this->getJson('/api/bookings/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

it('can update a booking', function () {
    $booking = Booking::factory()->create();
    $updatedData = Booking::factory()->make()->toArray();

    $response = $this->putJson("/api/bookings/{$booking->id}", $updatedData);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'tour_id' => $updatedData['tour_id'],
            'hotel_id' => $updatedData['hotel_id'],
            'customer_name' => $updatedData['customer_name'],
            'customer_email' => $updatedData['customer_email'],
        ]);
});

it('fails to update a booking with invalid data', function () {
    $booking = Booking::factory()->create();

    $response = $this->putJson("/api/bookings/{$booking->id}", 
        ['tour_id' => '', 'hotel_id' => '', 'customer_name' => '', 'customer_email' => '', 'number_of_people' => '', 'booking_date' => '']);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['tour_id', 'hotel_id', 'customer_name', 'customer_email', 'number_of_people', 'booking_date']);
});

it('can delete a booking', function () {
    $booking = Booking::factory()->create();

    $response = $this->deleteJson("/api/bookings/{$booking->id}");
    $response->assertStatus(Response::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
});

it('returns 404 when deleting a non-existent booking', function () {
    $response = $this->deleteJson('/api/bookings/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});
