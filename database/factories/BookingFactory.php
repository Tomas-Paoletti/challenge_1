<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\Hotel;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        return [
            'tour_id' => Tour::factory(),
            'hotel_id' => Hotel::factory(),
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->email,
            'number_of_people' => $this->faker->numberBetween(1, 10),
            'booking_date' => $this->faker->date(),
        ];
    }
}
