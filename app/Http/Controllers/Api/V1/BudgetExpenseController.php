<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Expense\CreateExpenseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ListExpensesRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/budgets/{budget}/expenses')]
#[Middleware('auth:sanctum')]
class BudgetExpenseController extends Controller
{
    #[Get('/', name: 'v1.budgets.expenses.index', middleware: 'can:view,budget')]
    public function index(ListExpensesRequest $request, Budget $budget): AnonymousResourceCollection
    {
        $expenses = $budget->expenses()
            ->with(['category', 'user'])
            ->inMonth($request->month())
            ->when($request->validated('category_id'), fn ($query, $category_id) => $query->where('category_id', $category_id))
            ->when($request->validated('user_id'), fn ($query, $user_id) => $query->where('user_id', $user_id))
            ->orderByDesc('spent_on')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return ExpenseResource::collection($expenses);
    }

    #[Post('/', name: 'v1.budgets.expenses.store', middleware: 'can:view,budget')]
    public function store(StoreExpenseRequest $request, Budget $budget, CreateExpenseAction $create_expense): JsonResponse
    {
        $expense = $create_expense->handle($budget, $request->user(), $request->validated());

        return ExpenseResource::make($expense->load(['category', 'user']))
            ->response()
            ->setStatusCode(201);
    }
}
