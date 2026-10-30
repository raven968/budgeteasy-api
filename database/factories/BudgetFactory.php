<?php

namespace Database\Factories;

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->randomElement(['Casa', 'Gastos del mes', 'Vacaciones', 'Despensa']),
            'amount' => fake()->numberBetween(500_000, 5_000_000),
            'currency' => 'MXN',
        ];
    }

    /**
     * Register the owner as a member, as the application does when creating a budget.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Budget $budget): void {
            $budget->members()->attach($budget->owner_id, ['role' => BudgetRole::Owner]);
        });
    }

    /**
     * Share the budget with the given user as a member.
     */
    public function sharedWith(User $user): static
    {
        return $this->afterCreating(function (Budget $budget) use ($user): void {
            $budget->members()->attach($user, ['role' => BudgetRole::Member]);
        });
    }
}
