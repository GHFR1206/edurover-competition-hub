<?php

namespace Database\Factories;

use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArenaAssignment>
 */
class ArenaAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'arena_id' => Arena::factory(),
            'user_id' => User::factory(),
            'role' => 'judge',
            'is_active' => true,
        ];
    }

    public function operator(): static
    {
        return $this->state(fn () => ['role' => 'operator']);
    }
}
