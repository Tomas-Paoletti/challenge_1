<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph,
            'address' => $this->faker->address,
            'rating' => $this->faker->numberBetween(1, 5),
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
