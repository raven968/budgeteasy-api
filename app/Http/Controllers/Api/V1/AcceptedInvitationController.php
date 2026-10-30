<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Invitation\AcceptInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Http\Resources\BudgetResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/invitations')]
#[Middleware('auth:sanctum')]
class AcceptedInvitationController extends Controller
{
    #[Post('accept', name: 'v1.invitations.accept', middleware: 'throttle:invitations')]
    public function store(AcceptInvitationRequest $request, AcceptInvitationAction $accept_invitation): BudgetResource
    {
        $budget = $accept_invitation->handle($request->user(), $request->validated('code'));

        return BudgetResource::make($budget->load('owner'));
    }
}
