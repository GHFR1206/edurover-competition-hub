<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => 'Tantangan '.fake()->word(),
            'theme' => fake()->word(),
            'description' => fake()->paragraph(),
            'mission_brief' => fake()->paragraph(),
            'position' => 0,
            'is_active' => true,
        ];
    }
}
