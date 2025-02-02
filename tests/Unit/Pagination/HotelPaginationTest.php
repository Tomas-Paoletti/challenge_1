<?php


namespace Tests\Unit\Pagination;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_paginated_hotels_with_default_per_page()
    {
        Hotel::factory(20)->create();

        $response = $this->getJson('/api/hotels');

        $response->assertStatus(200)
            ->assertJsonPath('meta', [
                'current_page' => 1,
                'total_pages' => 2,
                'total_items' => 20,
                'per_page' => 15
            ])
            ->assertJsonCount(15, 'data');
    }

    public function test_filters_by_rating_range()
    {
        Hotel::factory()->create(['rating' => 3]);
        Hotel::factory()->create(['rating' => 4]);
        Hotel::factory()->create(['rating' => 5]);

        $response = $this->getJson('/api/hotels?min_rating=4&max_rating=5');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_filters_by_price_range()
    {
        Hotel::factory()->create(['price_per_night' => 100]);
        Hotel::factory()->create(['price_per_night' => 200]);
        Hotel::factory()->create(['price_per_night' => 300]);

        $response = $this->getJson('/api/hotels?min_price=150&max_price=250');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_respects_custom_per_page_parameter()
    {
        Hotel::factory(20)->create();

        $response = $this->getJson('/api/hotels?per_page=5');

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
        $response = $this->getJson('/api/hotels?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_validates_per_page_maximum_value()
    {
        $response = $this->getJson('/api/hotels?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_validates_rating_range()
    {
        $response = $this->getJson('/api/hotels?min_rating=0&max_rating=6');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['min_rating', 'max_rating']);
    }

    public function test_validates_price_range()
    {
        $response = $this->getJson('/api/hotels?min_price=-1');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['min_price']);
    }

    public function test_can_navigate_through_pages()
    {
        Hotel::factory(30)->create();

        $response = $this->getJson('/api/hotels?page=2&per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta', [
                'current_page' => 2,
                'total_pages' => 3,
                'total_items' => 30,
                'per_page' => 10
            ]);
    }

    public function test_combines_filters()
    {
        Hotel::factory()->create([
            'rating' => 4,
            'price_per_night' => 150
        ]);
        Hotel::factory()->create([
            'rating' => 3,
            'price_per_night' => 200
        ]);
        Hotel::factory()->create([
            'rating' => 5,
            'price_per_night' => 300
        ]);

        $response = $this->getJson('/api/hotels?min_rating=4&max_price=250');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
