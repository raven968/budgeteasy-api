<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Services\BudgetSummary\BudgetSummary;
use App\Services\BudgetSummary\CategorySpending;
use App\Services\BudgetSummary\MemberSpending;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BudgetSummaryService
{
    /**
     * Spending of the budget in the given month, in total, per category and per member.
     *
     * Categories include every active category of the owner (even without spending) plus
     * deleted ones that still have expenses in the month. Members include current members
     * plus former ones who spent; expenses of deleted accounts are grouped under a null user.
     */
    public function forMonth(Budget $budget, CarbonImmutable $month): BudgetSummary
    {
        $spending_by_category = Expense::query()
            ->whereBelongsTo($budget)
            ->inMonth($month)
            ->selectRaw('category_id, sum(amount) as spent_amount, count(*) as expenses_count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $spending_by_user = Expense::query()
            ->whereBelongsTo($budget)
            ->inMonth($month)
            ->selectRaw('user_id, sum(amount) as spent_amount')
            ->groupBy('user_id')
            ->get()
            ->keyBy(fn (Expense $row) => $row->user_id ?? 0);

        $spent_amount = (int) $spending_by_category->sum('spent_amount');

        return new BudgetSummary(
            month: $month,
            budget: $budget,
            spent_amount: $spent_amount,
            remaining_amount: $budget->amount - $spent_amount,
            spent_percentage: round($spent_amount / $budget->amount * 100, 2),
            categories: $this->categoryBreakdown($budget, $spending_by_category),
            members: $this->memberBreakdown($budget, $spending_by_user),
        );
    }

    /**
     * @param  Collection<int, Expense>  $spending_by_category
     * @return Collection<int, CategorySpending>
     */
    private function categoryBreakdown(Budget $budget, Collection $spending_by_category): Collection
    {
        $limits = $budget->categoryLimits()->pluck('amount', 'category_id');

        return Category::withTrashed()
            ->where('user_id', $budget->owner_id)
            ->where(fn ($query) => $query
                ->whereNull('deleted_at')
                ->orWhereIn('id', $spending_by_category->keys()))
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => new CategorySpending(
                category: $category,
                spent_amount: (int) ($spending_by_category->get($category->id)?->spent_amount ?? 0),
                expenses_count: (int) ($spending_by_category->get($category->id)?->expenses_count ?? 0),
                limit_amount: $limits->get($category->id),
            ))
            ->sortByDesc(fn ($row) => $row->spent_amount)
            ->values();
    }

    /**
     * @param  Collection<int, Expense>  $spending_by_user
     * @return Collection<int, MemberSpending>
     */
    private function memberBreakdown(Budget $budget, Collection $spending_by_user): Collection
    {
        $user_ids = $budget->members()->pluck('users.id')
            ->merge($spending_by_user->keys()->filter())
            ->unique();

        $rows = User::whereIn('id', $user_ids)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => new MemberSpending(
                user: $user,
                spent_amount: (int) ($spending_by_user->get($user->id)?->spent_amount ?? 0),
            ));

        if ($spending_by_user->has(0)) {
            $rows->push(new MemberSpending(user: null, spent_amount: (int) $spending_by_user->get(0)->spent_amount));
        }

        return $rows->sortByDesc(fn ($row) => $row->spent_amount)->values();
    }
}
