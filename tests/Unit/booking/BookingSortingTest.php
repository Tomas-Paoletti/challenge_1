<?php

namespace Tests\Unit\booking;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingSortingTest extends TestCase
{
    use RefreshDatabase;

    private $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::factory()->create();
        $tour = Tour::factory()->create();


        Booking::factory()->create([
            'booking_date' => '2024-01-01',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'hotel_id' => $this->hotel->id,
            'tour_id' => $tour->id,
            'number_of_people' => 2,
            'created_at' => '2024-01-01 10:00:00'
        ]);

        Booking::factory()->create([
            'booking_date' => '2024-02-15',
            'customer_name' => 'Alice Smith',
            'customer_email' => 'alice@example.com',
            'hotel_id' => $this->hotel->id,
            'tour_id' => $tour->id,
            'number_of_people' => 4,
            'created_at' => '2024-01-02 10:00:00'
        ]);

        Booking::factory()->create([
            'booking_date' => '2024-03-30',
            'customer_name' => 'Bob Wilson',
            'customer_email' => 'bob@example.com',
            'hotel_id' => $this->hotel->id,
            'tour_id' => $tour->id,
            'number_of_people' => 1,
            'created_at' => '2024-01-03 10:00:00'
        ]);
    }

    public function test_default_sorting()
    {
        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200);

        $bookings = $response->json();

        $this->assertTrue(
            strtotime($bookings[0]['created_at']) >
            strtotime($bookings[1]['created_at'])
        );
    }

    public function test_sort_by_booking_date_asc()
    {
        $response = $this->getJson('/api/bookings?sort_by=booking_date&sort_order=asc');

        $response->assertStatus(200);
        $bookings = $response->json();

        $this->assertEquals('2024-01-01', $bookings[0]['booking_date']);
        $this->assertEquals('2024-02-15', $bookings[1]['booking_date']);
        $this->assertEquals('2024-03-30', $bookings[2]['booking_date']);
    }

    public function test_sort_by_booking_date_desc()
    {
        $response = $this->getJson('/api/bookings?sort_by=booking_date&sort_order=desc');

        $response->assertStatus(200);
        $bookings = $response->json();

        $this->assertEquals('2024-03-30', $bookings[0]['booking_date']);
        $this->assertEquals('2024-02-15', $bookings[1]['booking_date']);
        $this->assertEquals('2024-01-01', $bookings[2]['booking_date']);
    }

    public function test_sort_by_customer_name_asc()
    {
        $response = $this->getJson('/api/bookings?sort_by=customer_name&sort_order=asc');

        $response->assertStatus(200);

        $bookings = $response->json();
        $this->assertEquals('Alice Smith', $bookings[0]['customer_name']);
        $this->assertEquals('Bob Wilson', $bookings[1]['customer_name']);
        $this->assertEquals('John Doe', $bookings[2]['customer_name']);
    }



    public function test_invalid_sort_field()
    {
        $response = $this->getJson('/api/bookings?sort_by=invalid_field&sort_order=desc');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation error'
            ]);
    }

    public function test_invalid_sort_order()
    {
        $response = $this->getJson('/api/bookings?sort_by=booking_date&sort_order=invalid');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation error'
            ]);
    }


}
