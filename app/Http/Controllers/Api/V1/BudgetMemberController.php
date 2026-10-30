<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Budget\RemoveBudgetMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetMemberResource;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/budgets/{budget}/members')]
#[Middleware('auth:sanctum')]
class BudgetMemberController extends Controller
{
    #[Get('/', name: 'v1.budgets.members.index', middleware: 'can:view,budget')]
    public function index(Budget $budget): AnonymousResourceCollection
    {
        $members = $budget->members()->orderBy('budget_members.id')->get();

        return BudgetMemberResource::collection($members);
    }

    #[Delete('{member}', name: 'v1.budgets.members.destroy', middleware: ['can:view,budget', 'can:removeMember,budget,member'])]
    public function destroy(Budget $budget, User $member, RemoveBudgetMemberAction $remove_member): Response
    {
        $remove_member->handle($budget, $member);

        return response()->noContent();
    }
}
