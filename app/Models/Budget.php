<?php

namespace App\Models;

use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'amount', 'currency'])]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the user who created the budget.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users with access to the budget, including the owner.
     *
     * @return BelongsToMany<User, $this, BudgetMember>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'budget_members')
            ->using(BudgetMember::class)
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    /**
     * Get the categories available in the budget, which are the owner's categories.
     *
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_id', 'owner_id');
    }

    /**
     * @return HasMany<BudgetCategoryLimit, $this>
     */
    public function categoryLimits(): HasMany
    {
        return $this->hasMany(BudgetCategoryLimit::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * @return HasMany<BudgetInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(BudgetInvitation::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->id)->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }
}
