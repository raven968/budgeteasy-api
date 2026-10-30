<?php

namespace App\Actions\Invitation;

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\BudgetInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitationAction
{
    /**
     * Join the budget of the invitation as a member and consume the code.
     *
     * The invitation row is locked so a code can't be accepted twice concurrently.
     *
     * @throws ValidationException
     */
    public function handle(User $user, string $code): Budget
    {
        return DB::transaction(function () use ($user, $code): Budget {
            $invitation = BudgetInvitation::where('code', $code)->lockForUpdate()->first();
            $budget = $invitation?->budget;

            $error_key = match (true) {
                $budget === null => 'invitations.invalid',
                $invitation->accepted_at !== null => 'invitations.already_used',
                $invitation->expires_at->isPast() => 'invitations.expired',
                $budget->hasMember($user) => 'invitations.already_member',
                default => null,
            };

            if ($error_key) {
                throw ValidationException::withMessages(['code' => __($error_key)]);
            }

            $budget->members()->attach($user, ['role' => BudgetRole::Member]);

            $invitation->acceptedBy()->associate($user);
            $invitation->accepted_at = now();
            $invitation->save();

            return $budget;
        });
    }
}
