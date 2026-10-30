<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * Get the budgets the user created.
     *
     * @return HasMany<Budget, $this>
     */
    public function ownedBudgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'owner_id');
    }

    /**
     * Get every budget the user has access to, owned or shared.
     *
     * @return BelongsToMany<Budget, $this, BudgetMember>
     */
    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class, 'budget_members')
            ->using(BudgetMember::class)
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the expenses the user registered, across all budgets.
     *
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
