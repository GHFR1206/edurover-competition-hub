<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Rulebook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rulebook>
 */
class RulebookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'version' => 1,
            'title' => 'Rulebook v1 (CONTOH, bukan final rulebook)',
            'summary' => 'Aturan contoh untuk pengembangan. Bukan rulebook resmi.',
            'aggregation' => ['attempt' => 'best', 'judges' => 'average'],
            'tie_break_order' => ['primary_achievement', 'penalty', 'time', 'extra_run'],
            'penalties' => [],
            'retry_rules' => ['allow_re_run' => true, 'max_attempts' => 2],
            'status' => 'draft',
        ];
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'status' => 'locked',
            'locked_at' => now(),
        ]);
    }
}
