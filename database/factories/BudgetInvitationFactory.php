<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\BudgetInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BudgetInvitation>
 */
class BudgetInvitationFactory extends Factory
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
            'created_by' => fn (array $attributes) => Budget::findOrFail($attributes['budget_id'])->owner_id,
            'code' => Str::upper(Str::random(6)),
            'expires_at' => now()->addHours(48),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subMinute()]);
    }

    public function acceptedBy(User $user): static
    {
        return $this->state(fn () => [
            'accepted_by' => $user->id,
            'accepted_at' => now(),
        ]);
    }
}
