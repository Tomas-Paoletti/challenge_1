<?php

namespace Tests\Unit\booking;


use App\Enum\BookingStatusEnum;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_successfully_cancel_a_booking()
    {
        $tour = Tour::factory()->create();
        $hotel = Hotel::factory()->create();

        $booking = Booking::factory()->create([
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'booking_status' => BookingStatusEnum::PENDING->value
        ]);

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel");
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Booking successfully cancelled'
            ]);

        $booking->refresh();

        $this->assertEquals(
            BookingStatusEnum::CANCELLED->toString(),
            $booking->booking_status
        );
    }

    /** @test */
    public function cannot_cancel_an_already_cancelled_booking()
    {
        $tour = Tour::factory()->create();
        $hotel = Hotel::factory()->create();

        $booking = Booking::factory()->create([
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'booking_status' => BookingStatusEnum::CANCELLED->toString()
        ]);

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Booking is already cancelled'
            ]);
    }

    public function returns_404_for_non_existent_booking()
    {
        $nonExistentId = 9999;

        $response = $this->patchJson("/api/bookings/{$nonExistentId}/cancel");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Booking not found'
            ]);
    }
}
