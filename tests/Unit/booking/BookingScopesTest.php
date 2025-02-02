<?php

namespace Tests\Unit\booking;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingScopesTest extends TestCase
{
    use RefreshDatabase;

    private $bookings;
    private $hotel1;
    private $hotel2;
    private $tour1;
    private $tour2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel1 = Hotel::factory()->create();
        $this->hotel2 = Hotel::factory()->create();
        $this->tour1 = Tour::factory()->create();
        $this->tour2 = Tour::factory()->create();

        Booking::factory()->create([
            'booking_date' => '2024-01-01',
            'hotel_id' => $this->hotel1->id,
            'tour_id' => $this->tour1->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        Booking::factory()->create([
            'booking_date' => '2024-02-15',
            'hotel_id' => $this->hotel2->id,
            'tour_id' => $this->tour2->id,
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com'
        ]);
    }

    public function test_scope_between_dates()
    {
        $bookings = Booking::query()
            ->betweenDates('2024-01-01', '2024-02-01')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals('2024-01-01', $bookings->first()->booking_date);
    }

    public function test_scope_hotel()
    {
        $bookings = Booking::query()
            ->hotel($this->hotel1->id)
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals($this->hotel1->id, $bookings->first()->hotel_id);
    }

    public function test_scope_tour()
    {
        $bookings = Booking::query()
            ->tour($this->tour2->id)
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals($this->tour2->id, $bookings->first()->tour_id);
    }

    public function test_scope_customer_name()
    {
        $bookings = Booking::query()
            ->customerName('Jane')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals('Jane Smith', $bookings->first()->customer_name);
    }

    public function test_scope_customer_email()
    {
        $bookings = Booking::query()
            ->customerEmail('john@example.com')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals('john@example.com', $bookings->first()->customer_email);
    }



    public function test_scope_combination()
    {
        $bookings = Booking::query()
            ->hotel($this->hotel1->id)
            ->customerName('John')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals($this->hotel1->id, $bookings->first()->hotel_id);
        $this->assertEquals('John Doe', $bookings->first()->customer_name);
    }

    public function test_scope_case_insensitive_customer_name()
    {

        $bookings = Booking::query()
            ->customerName('jane')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals('Jane Smith', $bookings->first()->customer_name);
    }

    public function test_scope_case_insensitive_customer_email()
    {
        $bookings = Booking::query()
            ->customerEmail('JOHN@EXAMPLE.COM')
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals('john@example.com', $bookings->first()->customer_email);
    }

    public function test_scope_exact_booking_date()
    {
        $date = '2024-02-15';

        $bookings = Booking::query()
            ->where('booking_date', $date)
            ->get();

        $this->assertEquals(1, $bookings->count());
        $this->assertEquals($date, $bookings->first()->booking_date);
    }
}
