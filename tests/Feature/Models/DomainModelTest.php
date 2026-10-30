<?php

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\BudgetCategoryLimit;
use App\Models\BudgetInvitation;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('registers the owner as a member when a budget is created', function () {
    $budget = Budget::factory()->create();

    $member = $budget->members()->sole();

    expect($member->id)->toBe($budget->owner_id)
        ->and($member->pivot->role)->toBe(BudgetRole::Owner);
});

it('lists owned and shared budgets for a user', function () {
    $spouse = User::factory()->create();
    $owned = Budget::factory()->for($spouse, 'owner')->create();
    $shared = Budget::factory()->sharedWith($spouse)->create();
    Budget::factory()->create();

    expect($spouse->budgets()->pluck('budgets.id')->sort()->values()->all())
        ->toBe([$owned->id, $shared->id]);

    expect($spouse->budgets()->find($shared->id)->pivot->role)->toBe(BudgetRole::Member);
});

it('uses the owner categories in a shared budget', function () {
    $spouse = User::factory()->create();
    $budget = Budget::factory()->sharedWith($spouse)->create();
    $owner_category = Category::factory()->for($budget->owner)->create();
    Category::factory()->for($spouse)->create();

    expect($budget->categories()->pluck('id')->all())->toBe([$owner_category->id]);
});

it('keeps category names unique per user regardless of case', function () {
    $user = User::factory()->create();
    Category::factory()->for($user)->create(['name' => 'Súper']);

    Category::factory()->for($user)->create(['name' => 'súper']);
})->throws(UniqueConstraintViolationException::class);

it('allows reusing a category name after deleting it or for another user', function () {
    $user = User::factory()->create();
    Category::factory()->for($user)->create(['name' => 'Súper'])->delete();

    Category::factory()->for($user)->create(['name' => 'Súper']);
    Category::factory()->create(['name' => 'Súper']);

    expect(Category::withTrashed()->where('name', 'Súper')->count())->toBe(3);
});

it('keeps a soft deleted category on past expenses', function () {
    $expense = Expense::factory()->create();

    $expense->category->delete();

    expect($expense->fresh()->category)->not->toBeNull()
        ->and($expense->fresh()->category->trashed())->toBeTrue();
});

it('keeps the expenses of a member who deletes their account', function () {
    $spouse = User::factory()->create();
    $budget = Budget::factory()->sharedWith($spouse)->create();
    $expense = Expense::factory()->for($budget)->create(['user_id' => $spouse->id]);

    $spouse->delete();

    expect($expense->fresh()->user_id)->toBeNull();
});

it('deletes the owned budgets when the owner deletes their account', function () {
    $expense = Expense::factory()->create();
    $owner = $expense->budget->owner;

    $owner->delete();

    expect(Budget::withTrashed()->count())->toBe(0)
        ->and(Expense::withTrashed()->count())->toBe(0);
});

it('builds invitations and category limits tied to the budget owner', function () {
    $invitation = BudgetInvitation::factory()->expired()->create();
    $limit = BudgetCategoryLimit::factory()->create();

    expect($invitation->created_by)->toBe($invitation->budget->owner_id)
        ->and($invitation->expires_at->isPast())->toBeTrue()
        ->and($limit->category->user_id)->toBe($limit->budget->owner_id);
});
