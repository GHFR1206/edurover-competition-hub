<?php

namespace Database\Factories;

use App\Models\Arena;
use App\Models\Category;
use App\Models\HeatSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeatSlot>
 */
class HeatSlotFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addHour();

        return [
            'arena_id' => Arena::factory(),
            'category_id' => Category::factory(),
            'slot_no' => fake()->unique()->numberBetween(1, 999),
            'label' => 'Heat '.fake()->numberBetween(1, 20),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(15),
            'status' => 'scheduled',
        ];
    }
}
