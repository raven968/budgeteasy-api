<?php

namespace App\Actions\Budget;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RemoveBudgetMemberAction
{
    /**
     * Remove the user from the budget's members.
     *
     * @throws ModelNotFoundException when the user is not a member
     */
    public function handle(Budget $budget, User $member): void
    {
        $detached_count = $budget->members()->detach($member);

        if ($detached_count === 0) {
            throw (new ModelNotFoundException)->setModel(User::class, [$member->id]);
        }
    }
}
