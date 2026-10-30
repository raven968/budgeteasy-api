<?php

namespace App\Services\BudgetSummary;

use App\Models\Budget;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Spending of a budget in one month, produced by BudgetSummaryService.
 */
final readonly class BudgetSummary
{
    /**
     * @param  Collection<int, CategorySpending>  $categories
     * @param  Collection<int, MemberSpending>  $members
     */
    public function __construct(
        public CarbonImmutable $month,
        public Budget $budget,
        public int $spent_amount,
        public int $remaining_amount,
        public float $spent_percentage,
        public Collection $categories,
        public Collection $members,
    ) {}
}
