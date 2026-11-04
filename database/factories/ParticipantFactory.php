<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        $nisn = (string) fake()->unique()->numberBetween(1000000000, 9999999999);

        return [
            'team_id' => Team::factory(),
            'name' => fake()->name(),
            'nisn' => $nisn,
            'nisn_hash' => Participant::hashNisn($nisn),
            'grade' => (string) fake()->numberBetween(1, 12),
            'role' => 'member',
            'birth_date' => fake()->date(),
        ];
    }
}
