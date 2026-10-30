<?php

namespace App\Http\Resources;

use App\Services\BudgetSummary\BudgetSummary;
use App\Services\BudgetSummary\CategorySpending;
use App\Services\BudgetSummary\MemberSpending;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BudgetSummary
 */
class BudgetSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 2026-10 */
            'month' => $this->month->format('Y-m'),
            'currency' => $this->budget->currency,
            'budget_amount' => $this->budget->amount,
            'spent_amount' => $this->spent_amount,
            /** Negative when the budget is exceeded. */
            'remaining_amount' => $this->remaining_amount,
            'spent_percentage' => $this->spent_percentage,
            'categories' => $this->categories->map(fn (CategorySpending $row) => [
                'category' => CategoryResource::make($row->category),
                'spent_amount' => $row->spent_amount,
                'expenses_count' => $row->expenses_count,
                'limit_amount' => $row->limit_amount,
            ]),
            'members' => $this->members->map(fn (MemberSpending $row) => [
                'user' => $row->user ? ['id' => $row->user->id, 'name' => $row->user->name] : null,
                'spent_amount' => $row->spent_amount,
            ]),
        ];
    }
}
