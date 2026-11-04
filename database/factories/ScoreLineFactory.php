<?php

namespace Database\Factories;

use App\Models\Score;
use App\Models\ScoreLine;
use App\Models\ScoreObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScoreLine>
 */
class ScoreLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'score_id' => Score::factory(),
            'score_object_id' => ScoreObject::factory(),
            'quantity' => 1,
            'achieved' => true,
            'points_per_unit_snapshot' => 10,
            'computed_points' => 10,
        ];
    }
}
