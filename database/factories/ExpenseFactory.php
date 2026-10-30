<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Category and author default to the budget owner, keeping the factory consistent
     * with the rule that a budget uses its owner's categories.
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
            'user_id' => fn (array $attributes) => Budget::findOrFail($attributes['budget_id'])->owner_id,
            'amount' => fake()->numberBetween(1_000, 300_000),
            'spent_on' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'note' => fake()->optional()->sentence(4),
        ];
    }
}
