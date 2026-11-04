<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Kompetisi Robot '.fake()->year();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'venue' => fake()->city(),
            'starts_at' => now()->addMonth()->toDateString(),
            'ends_at' => now()->addMonth()->addDays(2)->toDateString(),
            'status' => 'draft',
        ];
    }
}
