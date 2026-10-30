<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Budget;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/budgets/{budget}/categories')]
#[Middleware('auth:sanctum')]
class BudgetCategoryController extends Controller
{
    /**
     * The categories members can use in the budget: the owner's active categories.
     */
    #[Get('/', name: 'v1.budgets.categories.index', middleware: 'can:view,budget')]
    public function index(Budget $budget): AnonymousResourceCollection
    {
        return CategoryResource::collection($budget->categories()->orderBy('name')->get());
    }
}
