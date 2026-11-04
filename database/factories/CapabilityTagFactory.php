<?php

namespace Database\Factories;

use App\Models\CapabilityTag;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapabilityTag>
 */
class CapabilityTagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'code' => 'cap_'.fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'position' => 0,
        ];
    }
}
