<?php

namespace App\Http\Resources;

use App\Services\BudgetSummaryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formats the array produced by BudgetSummaryService::forMonth().
 *
 * @see BudgetSummaryService::forMonth()
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
        $summary = $this->resource;

        return [
            'month' => $summary['month']->format('Y-m'),
            'currency' => $summary['budget']->currency,
            'budget_amount' => $summary['budget']->amount,
            'spent_amount' => $summary['spent_amount'],
            'remaining_amount' => $summary['remaining_amount'],
            'spent_percentage' => $summary['spent_percentage'],
            'categories' => $summary['categories']->map(fn (array $row) => [
                'category' => CategoryResource::make($row['category']),
                'spent_amount' => $row['spent_amount'],
                'expenses_count' => $row['expenses_count'],
                'limit_amount' => $row['limit_amount'],
            ]),
            'members' => $summary['members']->map(fn (array $row) => [
                'user' => $row['user'] ? ['id' => $row['user']->id, 'name' => $row['user']->name] : null,
                'spent_amount' => $row['spent_amount'],
            ]),
        ];
    }
}
