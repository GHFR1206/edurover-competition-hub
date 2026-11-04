<?php

namespace Database\Factories;

use App\Models\Run;
use App\Models\TelemetryEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelemetryEvent>
 */
class TelemetryEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'device_id' => 'edu-rover-'.fake()->numberBetween(1, 50),
            'event_type' => 'checkpoint_reached',
            'payload' => ['checkpoint' => fake()->numberBetween(1, 6)],
            'occurred_at' => now(),
            'received_at' => now(),
        ];
    }
}
