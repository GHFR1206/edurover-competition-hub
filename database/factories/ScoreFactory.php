<?php

namespace Database\Factories;

use App\Enums\ScoreStatus;
use App\Models\Run;
use App\Models\Score;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Score>
 */
class ScoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'judge_id' => User::factory(),
            'version' => 1,
            'status' => ScoreStatus::Draft,
            'subtotal' => 0,
            'penalty_total' => 0,
            'total' => 0,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ScoreStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ScoreStatus::Approved,
            'submitted_at' => now(),
            'approved_at' => now(),
        ]);
    }
}
