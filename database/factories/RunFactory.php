<?php

namespace Database\Factories;

use App\Enums\RunStatus;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Run>
 */
class RunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'category_id' => Category::factory(),
            'arena_id' => Arena::factory(),
            'team_id' => Team::factory(),
            'attempt_no' => 1,
            'status' => RunStatus::Scheduled,
            'scheduled_at' => now()->addHour(),
        ];
    }

    public function finished(): static
    {
        return $this->state(fn () => [
            'status' => RunStatus::Finished,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(2),
            'duration_seconds' => 480,
        ]);
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'status' => RunStatus::Running,
            'started_at' => now()->subMinutes(3),
        ]);
    }
}
