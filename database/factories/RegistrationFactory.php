<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'category_id' => Category::factory(),
            'team_id' => Team::factory(),
            'status' => RegistrationStatus::Verified,
            'consent_parent' => true,
            'consent_media' => true,
            'consent_at' => now(),
            'submitted_at' => now(),
            'verified_at' => now(),
        ];
    }
}
