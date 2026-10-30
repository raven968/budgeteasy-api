<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Budget\CreateBudgetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

#[Prefix('v1/budgets')]
#[Middleware('auth:sanctum')]
class BudgetController extends Controller
{
    #[Get('/', name: 'v1.budgets.index')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $budgets = $request->user()->budgets()->with('owner')->orderBy('name')->get();

        return BudgetResource::collection($budgets);
    }

    #[Post('/', name: 'v1.budgets.store')]
    public function store(StoreBudgetRequest $request, CreateBudgetAction $create_budget): JsonResponse
    {
        $budget = $create_budget->handle($request->user(), $request->validated());

        return BudgetResource::make($budget->load('owner'))
            ->response()
            ->setStatusCode(201);
    }

    #[Get('{budget}', name: 'v1.budgets.show', middleware: 'can:view,budget')]
    public function show(Budget $budget): BudgetResource
    {
        return BudgetResource::make($budget->load('owner'));
    }

    #[Put('{budget}', name: 'v1.budgets.update', middleware: 'can:update,budget')]
    public function update(UpdateBudgetRequest $request, Budget $budget): BudgetResource
    {
        $budget->update($request->validated());

        return BudgetResource::make($budget->load('owner'));
    }

    #[Delete('{budget}', name: 'v1.budgets.destroy', middleware: 'can:delete,budget')]
    public function destroy(Budget $budget): Response
    {
        $budget->delete();

        return response()->noContent();
    }
}
