<?php

namespace App\Services\BudgetSummary;

use App\Models\User;

/**
 * Spending of one user; `user` is null for expenses of deleted accounts.
 */
final readonly class MemberSpending
{
    public function __construct(
        public ?User $user,
        public int $spent_amount,
    ) {}
}
