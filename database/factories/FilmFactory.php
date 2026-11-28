<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilmFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'country_id' => Country::factory(),
            'duration' => fake()->numberBetween(60, 200),
            'year_of_issue' => fake()->numberBetween(1990, 2024),
            'age' => fake()->randomElement([0, 6, 12, 16, 18]),
            'link_img' => null,
            'link_kinopoisk' => null,
            'link_video' => fake()->url(),
        ];
    }
}
