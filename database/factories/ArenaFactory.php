<?php

namespace Database\Factories;

use App\Models\Arena;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Arena>
 */
class ArenaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'Arena '.fake()->numberBetween(1, 999),
            // The code is unique per event and seeded arenas already occupy
            // ARENA-1..N, so a numeric random code collides intermittently.
            'code' => 'ARENA-'.strtoupper(Str::random(8)),
            'location' => fake()->city(),
            'status' => 'idle',
            'capacity_per_slot' => 1,
            'slot_minutes' => 15,
            'is_active' => true,
        ];
    }
}
