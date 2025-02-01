<?php

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can create a hotel', function () {
    $hotelData = Hotel::factory()->make()->toArray();

    $response = $this->postJson('/api/hotels', $hotelData);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment([
            'name' => $hotelData['name'],
            'description' => $hotelData['description'],
            'address' => $hotelData['address'],
            'rating' => $hotelData['rating'],
        ]);
});

it('fails to create a hotel with invalid data', function () {
    $response = $this->postJson('/api/hotels', []);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'description', 'address', 'rating', 'price_per_night']);
});

it('can retrieve all hotels', function () {
    $hotels = Hotel::factory()->count(3)->create();

    $response = $this->getJson('/api/hotels');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(3);
});

it('can retrieve hotels with filters', function () {
    $hotel1 = Hotel::factory()->create(['rating' => 2, 'price_per_night' => 100]);
    $hotel2 = Hotel::factory()->create(['rating' => 4, 'price_per_night' => 200]);
    $hotel3 = Hotel::factory()->create(['rating' => 5, 'price_per_night' => 300]);

    $response = $this->getJson('/api/hotels?min_rating=3&max_rating=5&min_price=150&max_price=250');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment(['id' => $hotel2->id])
        ->assertJsonMissing(['id' => $hotel1->id])
        ->assertJsonMissing(['id' => $hotel3->id]);
});

it('can retrieve a single hotel', function () {
    $hotel = Hotel::factory()->create();

    $response = $this->getJson("/api/hotels/{$hotel->id}");
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'id' => $hotel->id,
            'name' => $hotel->name,
            'description' => $hotel->description,
            'address' => $hotel->address,
            'rating' => $hotel->rating,
        ]);
});

it('returns 404 for a non-existent hotel', function () {
    $response = $this->getJson('/api/hotels/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

it('can update a hotel', function () {
    $hotel = Hotel::factory()->create();
    $updatedData = Hotel::factory()->make()->toArray();

    $response = $this->putJson("/api/hotels/{$hotel->id}", $updatedData);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
            'address' => $updatedData['address'],
            'rating' => $updatedData['rating'],
        ]);
});

it('fails to update a hotel with invalid data', function () {
    $hotel = Hotel::factory()->create();

    $response = $this->putJson("/api/hotels/{$hotel->id}", 
        ['name' => '', 'description' => '', 'address' => '', 'rating' => '', 'price_per_night' => '']
    );
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['name', 'description', 'address', 'rating', 'price_per_night']);
});

it('can delete a hotel', function () {
    $hotel = Hotel::factory()->create();

    $response = $this->deleteJson("/api/hotels/{$hotel->id}");
    $response->assertStatus(Response::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing('hotels', ['id' => $hotel->id]);
});

it('returns 404 when deleting a non-existent hotel', function () {
    $response = $this->deleteJson('/api/hotels/999');
    $response->assertStatus(Response::HTTP_NOT_FOUND);
});
