<?php

namespace App\Actions\Expense;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;

class CreateExpenseAction
{
    /**
     * Register an expense in the budget on behalf of the user.
     *
     * @param  array{category_id: int, amount: int, spent_on: string, note?: string|null}  $data
     */
    public function handle(Budget $budget, User $user, array $data): Expense
    {
        $expense = $budget->expenses()->make($data);
        $expense->user()->associate($user);
        $expense->save();

        return $expense;
    }
}
