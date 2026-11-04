<?php

namespace Database\Factories;

use App\Models\ResultApproval;
use App\Models\Run;
use App\Models\Score;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResultApproval>
 */
class ResultApprovalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'score_id' => Score::factory(),
            'run_id' => Run::factory(),
            'decision' => 'approved',
            'decided_by' => User::factory(),
            'decided_at' => now(),
        ];
    }
}
