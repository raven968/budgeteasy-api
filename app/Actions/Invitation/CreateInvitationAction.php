<?php

namespace App\Actions\Invitation;

use App\Models\Budget;
use App\Models\BudgetInvitation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CreateInvitationAction
{
    private const MAX_ATTEMPTS = 5;

    /**
     * Create a single-use invitation code for the budget, expiring any pending one.
     */
    public function handle(Budget $budget, User $creator): BudgetInvitation
    {
        return DB::transaction(function () use ($budget, $creator): BudgetInvitation {
            $budget->invitations()->pending()->update(['expires_at' => now()]);

            return retry(self::MAX_ATTEMPTS, function () use ($budget, $creator): BudgetInvitation {
                return DB::transaction(function () use ($budget, $creator): BudgetInvitation {
                    $invitation = $budget->invitations()->make([
                        'code' => BudgetInvitation::generateCode(),
                        'expires_at' => now()->addHours(BudgetInvitation::TTL_HOURS),
                    ]);
                    $invitation->creator()->associate($creator);
                    $invitation->save();

                    return $invitation;
                });
            }, when: fn ($exception) => $exception instanceof UniqueConstraintViolationException);
        });
    }
}
