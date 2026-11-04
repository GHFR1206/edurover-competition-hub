<?php

namespace Database\Factories;

use App\Models\EvidenceFile;
use App\Models\Run;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvidenceFile>
 */
class EvidenceFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'disk' => 'local',
            'path' => 'evidence/'.fake()->uuid().'.jpg',
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(100000, 5000000),
            'kind' => 'photo',
            'caption' => fake()->sentence(),
        ];
    }
}
