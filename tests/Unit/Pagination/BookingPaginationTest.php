<?php

namespace Tests\Unit\Pagination;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class BookingPaginationTest extends TestCase
{
    use RefreshDatabase;


    public function test_returns_paginated_bookings_with_default_per_page()
    {
        Booking::factory(20)->create();

        $response = $this->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonPath('meta', [
                'current_page' => 1,
                'total_pages' => 2,
                'total_items' => 20,
                'per_page' => 15
            ])
            ->assertJsonCount(15, 'data');

    }

    /** @test */
    public function test_respects_custom_per_page_parameter()
    {
        Booking::factory(20)->create();

        $response = $this->getJson('/api/bookings?per_page=5');

        $response->assertStatus(200)

                 ->assertJsonPath('meta', [
                     'current_page' => 1,
                     'total_pages' => 4,
                     'total_items' => 20,
                     'per_page' => 5
                 ])
            ->assertJsonCount(5, 'data');

    }

    public function test_validates_per_page_minimum_value()
    {
        $response = $this->getJson('/api/bookings?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_validates_per_page_maximum_value()
    {
        $response = $this->getJson('/api/bookings?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_can_navigate_through_pages()
    {
        Booking::factory(30)->create();

        $response = $this->getJson('/api/bookings?page=2&per_page=10');
        $response->assertStatus(200)
->assertJsonPath('meta', [
               'current_page' => 2,
               'total_pages' => 3,
               'total_items' => 30,
               'per_page' => 10
           ]);

    }

    public function test_returns_empty_data_array_when_page_exceeds_last_page()
    {
        Booking::factory(15)->create();

        $response = $this->getJson('/api/bookings?page=10');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ])
            ->assertJson([
                'message' => 'No bookings found'
            ]);
    }
}
