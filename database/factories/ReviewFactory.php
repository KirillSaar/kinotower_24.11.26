<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'film_id' => Film::factory(),
            'user_id' => User::factory(),
            'message' => fake()->sentence(),
            'is_approved' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['is_approved' => 1]);
    }
}
