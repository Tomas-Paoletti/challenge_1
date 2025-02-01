<?php

namespace Tests\Unit\booking;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BookingExportCsvTest extends  TestCase
{
    use RefreshDatabase;

    public function test_export_endpoint_returns_csv()
    {
        Excel::fake();

        $tour = Tour::factory()->create();
        $hotel = Hotel::factory()->create();

        Booking::factory()->create([
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'booking_date' => '2024-02-15'
        ]);

        $response = $this->get('/api/bookings/export');

        $response->assertStatus(200);

        Excel::assertDownloaded('bookings.csv');
    }

    public function test_export_with_no_data()
    {
        Excel::fake();

        $response = $this->get('/api/bookings/export');

        $response->assertStatus(200);
        Excel::assertDownloaded('bookings.csv');
    }

    public function test_export_handles_error()
    {
        Excel::shouldReceive('download')
            ->andThrow(new \Exception('Test error'));

        $response = $this->get('/api/bookings/export');

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Error exporting bookings'
            ]);
    }
}
