<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => ucfirst(fake()->unique()->word()),
            'icon' => fake()->randomElement(['cart', 'car', 'home', 'food', 'heart', 'gift']),
            'color' => fake()->hexColor(),
        ];
    }
}
