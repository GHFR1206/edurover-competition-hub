<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Category;
use App\Models\School;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'Golf', 'Hotel',
            'India', 'Juliet', 'Kilo', 'Lima', 'Mike', 'November', 'Oscar', 'Papa',
        ]).' '.fake()->numberBetween(1, 99);

        return [
            'school_id' => School::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_name' => strtoupper(Str::substr($name, 0, 3)),
            'status' => RegistrationStatus::Verified,
        ];
    }
}
