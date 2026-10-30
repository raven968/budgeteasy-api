<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    /**
     * Members can see the budget, its categories, members and expenses.
     */
    public function view(User $user, Budget $budget): bool
    {
        return $budget->hasMember($user);
    }

    /**
     * Determine whether the user can update the budget.
     */
    public function update(User $user, Budget $budget): bool
    {
        return $budget->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the budget.
     */
    public function delete(User $user, Budget $budget): bool
    {
        return $budget->isOwnedBy($user);
    }

    /**
     * The owner can remove any member and a member can leave; the owner can't be removed.
     */
    public function removeMember(User $user, Budget $budget, User $member): bool
    {
        if ($budget->isOwnedBy($member)) {
            return false;
        }

        return $budget->isOwnedBy($user) || $user->is($member);
    }
}
