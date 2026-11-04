<?php

namespace Database\Factories;

use App\Enums\Level;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'SD Negeri '.fake()->numberBetween(1, 200).' '.fake()->city(),
            'npsn' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'level' => Level::Sd,
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->unique()->safeEmail(),
        ];
    }
}
