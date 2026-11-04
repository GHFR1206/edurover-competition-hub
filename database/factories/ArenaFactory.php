<?php

namespace Database\Factories;

use App\Models\Arena;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Arena>
 */
class ArenaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'Arena '.fake()->unique()->numberBetween(1, 10),
            'code' => 'ARENA-'.fake()->unique()->numberBetween(1, 99),
            'location' => fake()->city(),
            'status' => 'idle',
            'capacity_per_slot' => 1,
            'slot_minutes' => 15,
            'is_active' => true,
        ];
    }
}
