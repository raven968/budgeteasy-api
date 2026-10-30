<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Invitation\CreateInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetInvitationResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/budgets/{budget}/invitations')]
#[Middleware('auth:sanctum')]
class BudgetInvitationController extends Controller
{
    #[Post('/', name: 'v1.budgets.invitations.store', middleware: 'can:invite,budget')]
    public function store(Request $request, Budget $budget, CreateInvitationAction $create_invitation): JsonResponse
    {
        $invitation = $create_invitation->handle($budget, $request->user());

        return BudgetInvitationResource::make($invitation)
            ->response()
            ->setStatusCode(201);
    }
}
