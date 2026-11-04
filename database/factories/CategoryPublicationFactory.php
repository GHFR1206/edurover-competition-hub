<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryPublication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryPublication>
 */
class CategoryPublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'published_at' => now(),
            'published_by' => User::factory(),
        ];
    }
}
