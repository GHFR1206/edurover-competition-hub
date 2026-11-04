<?php

namespace Database\Factories;

use App\Enums\ScoreObjectType;
use App\Models\Challenge;
use App\Models\ScoreObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScoreObject>
 */
class ScoreObjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'code' => 'obj_'.fake()->unique()->numberBetween(1, 9999),
            'name' => 'Objek '.fake()->word(),
            'description' => fake()->sentence(),
            'type' => ScoreObjectType::Object,
            'points_per_unit' => fake()->numberBetween(5, 50),
            'max_quantity' => null,
            'requires_evidence' => false,
            'is_bonus' => false,
            'position' => 0,
        ];
    }

    public function penalty(): static
    {
        return $this->state(fn () => ['type' => ScoreObjectType::Penalty]);
    }

    public function checkpoint(): static
    {
        return $this->state(fn () => ['type' => ScoreObjectType::Checkpoint]);
    }
}
