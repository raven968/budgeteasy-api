<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\BudgetCategoryLimit;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetCategoryLimit>
 */
class BudgetCategoryLimitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'category_id' => fn (array $attributes) => Category::factory()->create([
                'user_id' => Budget::findOrFail($attributes['budget_id'])->owner_id,
            ]),
            'amount' => fake()->numberBetween(100_000, 1_000_000),
        ];
    }
}
