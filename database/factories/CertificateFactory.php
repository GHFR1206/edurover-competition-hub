<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'team_id' => Team::factory(),
            'type' => 'participation',
            'qr_token' => Str::random(32),
            'issued_at' => now(),
        ];
    }
}
