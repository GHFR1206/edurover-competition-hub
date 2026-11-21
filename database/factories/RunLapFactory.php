<?php

namespace Database\Factories;

use App\Models\Run;
use App\Models\RunLap;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RunLap> */
class RunLapFactory extends Factory
{
    protected $model = RunLap::class;

    public function definition(): array
    {
        $lap = fake()->numberBetween(8_000, 30_000);

        return [
            'run_id' => Run::factory(),
            'lap_no' => 1,
            'lap_ms' => $lap,
            'cumulative_ms' => $lap,
            'recorded_at' => now(),
            'source' => 'api',
            'device_id' => 'EDU-'.fake()->numerify('###'),
        ];
    }
}
