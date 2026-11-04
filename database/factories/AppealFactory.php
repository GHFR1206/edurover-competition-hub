<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appeal>
 */
class AppealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'team_id' => Team::factory(),
            'reason' => fake()->sentence(),
            'status' => 'submitted',
        ];
    }
}
