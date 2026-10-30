<?php

namespace App\Services\BudgetSummary;

use App\Models\Category;

final readonly class CategorySpending
{
    public function __construct(
        public Category $category,
        public int $spent_amount,
        public int $expenses_count,
        public ?int $limit_amount,
    ) {}
}
