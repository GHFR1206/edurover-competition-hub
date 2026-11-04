<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action' => 'created',
            'actor_name' => fake()->name(),
            'before' => null,
            'after' => ['example' => true],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
