<?php

namespace Database\Factories;

use App\Enums\Level;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $level = fake()->randomElement(Level::cases());

        return [
            'event_id' => Event::factory(),
            'level' => $level,
            'name' => $level->label().' — '.$level->persona(),
            'persona' => $level->persona(),
            'description' => fake()->paragraph(),
            'status' => 'registration',
            'min_team_members' => 1,
            'max_team_members' => 3,
            'settings' => ['aggregation' => 'best'],
        ];
    }

    public function forLevel(Level $level): static
    {
        return $this->state(fn () => [
            'level' => $level,
            'name' => $level->label().' — '.$level->persona(),
            'persona' => $level->persona(),
        ]);
    }
}
