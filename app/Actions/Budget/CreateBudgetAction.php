<?php

namespace App\Actions\Budget;

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateBudgetAction
{
    /**
     * Create a budget owned by the user and register them as its owner member.
     *
     * @param  array{name: string, amount: int, currency: string}  $data
     */
    public function handle(User $user, array $data): Budget
    {
        return DB::transaction(function () use ($user, $data): Budget {
            $budget = $user->ownedBudgets()->create($data);

            $budget->members()->attach($user, ['role' => BudgetRole::Owner]);

            return $budget;
        });
    }
}
