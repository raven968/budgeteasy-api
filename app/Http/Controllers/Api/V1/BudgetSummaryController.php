<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\ShowBudgetSummaryRequest;
use App\Http\Resources\BudgetSummaryResource;
use App\Models\Budget;
use App\Services\BudgetSummaryService;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/budgets/{budget}/summary')]
#[Middleware('auth:sanctum')]
class BudgetSummaryController extends Controller
{
    #[Get('/', name: 'v1.budgets.summary', middleware: 'can:view,budget')]
    public function __invoke(ShowBudgetSummaryRequest $request, Budget $budget, BudgetSummaryService $budget_summary): BudgetSummaryResource
    {
        return BudgetSummaryResource::make($budget_summary->forMonth($budget, $request->month()));
    }
}
