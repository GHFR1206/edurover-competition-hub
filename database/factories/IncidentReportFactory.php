<?php

namespace Database\Factories;

use App\Models\IncidentReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentReport>
 */
class IncidentReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'severity' => 'info',
            'category' => 'technical',
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'open',
        ];
    }
}
