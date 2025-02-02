<?php


namespace Tests\Unit\Pagination;

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_paginated_tours_with_default_per_page()
    {
        Tour::factory(20)->create();

        $response = $this->getJson('/api/tours');

        $response->assertStatus(200)
            ->assertJsonPath('meta', [
                'current_page' => 1,
                'total_pages' => 2,
                'total_items' => 20,
                'per_page' => 15
            ])
            ->assertJsonCount(15, 'data');
    }

    public function test_filters_by_price_range()
    {
        Tour::factory()->create(['price' => 100]);
        Tour::factory()->create(['price' => 200]);
        Tour::factory()->create(['price' => 300]);

        $response = $this->getJson('/api/tours?min_price=150&max_price=250');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filters_by_date_range()
    {
        Tour::factory()->create([
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-10'
        ]);
        Tour::factory()->create([
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-10'
        ]);
        Tour::factory()->create([
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-10'
        ]);

        $response = $this->getJson('/api/tours?start_date=2025-02-01&end_date=2025-02-28');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_validates_end_date_after_start_date()
    {
        $response = $this->getJson('/api/tours?start_date=2025-02-01&end_date=2025-01-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_validates_negative_price()
    {
        $response = $this->getJson('/api/tours?min_price=-100');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['min_price']);
    }

    public function test_respects_custom_per_page_parameter()
    {
        Tour::factory(20)->create();

        $response = $this->getJson('/api/tours?per_page=5');

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
        $response = $this->getJson('/api/tours?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_validates_per_page_maximum_value()
    {
        $response = $this->getJson('/api/tours?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_can_navigate_through_pages()
    {
        Tour::factory(30)->create();

        $response = $this->getJson('/api/tours?page=2&per_page=10');

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
        Tour::factory()->create([
            'price' => 150,
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-10'
        ]);
        Tour::factory()->create([
            'price' => 200,
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-10'
        ]);
        Tour::factory()->create([
            'price' => 250,
            'start_date' => '2025-04-01',
            'end_date' => '2025-04-10'
        ]);

        $response = $this->getJson('/api/tours?min_price=100&max_price=200&start_date=2025-02-01&end_date=2025-03-31');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_returns_validation_error_with_invalid_date_format()
    {
        $response = $this->getJson('/api/tours?start_date=invalid-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_handles_empty_result_set()
    {
        Tour::factory()->create(['price' => 100]);

        $response = $this->getJson('/api/tours?min_price=200');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'total_pages',
                    'total_items',
                    'per_page'
                ]
            ]);
    }
}
