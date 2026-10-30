<?php

namespace App\Models;

use Database\Factories\BudgetInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'expires_at'])]
class BudgetInvitation extends Model
{
    /**
     * Characters used in codes; excludes look-alikes such as 0/O and 1/I/L.
     */
    public const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public const CODE_LENGTH = 6;

    public const TTL_HOURS = 48;

    /** @use HasFactory<BudgetInvitationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Budget, $this>
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /**
     * Generate a random code; uniqueness is guaranteed by the database index.
     */
    public static function generateCode(): string
    {
        $last_index = strlen(self::CODE_ALPHABET) - 1;

        return collect(range(1, self::CODE_LENGTH))
            ->map(fn () => self::CODE_ALPHABET[random_int(0, $last_index)])
            ->implode('');
    }

    /**
     * Invitations that can still be accepted.
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }
}
