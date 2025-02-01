<?php

use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can create a tour', function () {
    $tourData = Tour::factory()->make()->toArray();

    $response = $this->postJson('/api/tours', $tourData);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment([
            'name' => $tourData['name'],
            'description' => $tourData['description'],
            'price' => $tourData['price'],
        ]);
});

it('fails to create a tour with invalid data', function () {
    $response = $this->postJson('/api/tours', []);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'description', 'price', 'start_date', 'end_date']);
});

it('can retrieve all tours', function () {
    $tours = Tour::factory()->count(3)->create();

    $response = $this->getJson('/api/tours');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(3);
});

it('can retrieve tours with filters', function () {
    $tour1 = Tour::factory()->create(['price' => 100, 'start_date' => now()->subDays(2), 'end_date' => now()->subDay()]);
    $tour2 = Tour::factory()->create(['price' => 200, 'start_date' => now()->subDays(1), 'end_date' => now()]);
    $tour3 = Tour::factory()->create(['price' => 300, 'start_date' => now(), 'end_date' => now()->addDay()]);

    $response = $this->getJson('/api/tours?min_price=150&max_price=250&start_date=' . now()->subDays(1)->toDateString() . '&end_date=' . now()->toDateString());
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment(['id' => $tour2->id])
        ->assertJsonMissing(['id' => $tour1->id])
        ->assertJsonMissing(['id' => $tour3->id]);
});

it('can retrieve a single tour', function () {
    $tour = Tour::factory()->create();

    $response = $this->getJson("/api/tours/{$tour->id}");
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'id' => $tour->id,
            'name' => $tour->name,
            'description' => $tour->description,
            'price' => $tour->price,
        ]);
});

it('returns 404 for a non-existent tour', function () {
    $response = $this->getJson('/api/tours/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

it('can update a tour', function () {
    $tour = Tour::factory()->create();
    $updatedData = Tour::factory()->make()->toArray();

    $response = $this->putJson("/api/tours/{$tour->id}", $updatedData);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
            'price' => $updatedData['price'],
        ]);
});

it('fails to update a tour with invalid data', function () {
    $tour = Tour::factory()->create();

    $response = $this->putJson("/api/tours/{$tour->id}", 
        ['name' => '', 'description' => '', 'price' => '', 'start_date' => '', 'end_date' => '']
    );
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'description', 'price', 'start_date', 'end_date']);
});

it('can delete a tour', function () {
    $tour = Tour::factory()->create();

    $response = $this->deleteJson("/api/tours/{$tour->id}");
    $response->assertStatus(Response::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('tours', ['id' => $tour->id]);
});

it('returns 404 when deleting a non-existent tour', function () {
    $response = $this->deleteJson('/api/tours/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});
