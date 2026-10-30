<?php

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->budget = Budget::factory()->for($this->owner, 'owner')->sharedWith($this->member)->create();
    $this->expense = Expense::factory()->for($this->budget)->create(['user_id' => $this->member->id, 'amount' => 1_000]);
});

describe('update', function () {
    it('lets the author and the owner update the expense', function (string $actor) {
        Sanctum::actingAs($this->{$actor});
        $category = Category::factory()->for($this->owner)->create();

        $this->putJson("/api/v1/expenses/{$this->expense->id}", ['amount' => 2_500, 'category_id' => $category->id, 'note' => null])
            ->assertOk()
            ->assertJsonPath('data.amount', 2_500)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.note', null);
    })->with(['member', 'owner']);

    it('rejects a category that is not from the budget owner', function () {
        Sanctum::actingAs($this->member);
        $category = Category::factory()->for($this->member)->create();

        $this->putJson("/api/v1/expenses/{$this->expense->id}", ['category_id' => $category->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    });

    it('forbids another member, a former member and an outsider', function (string $case) {
        $actor = User::factory()->create();
        match ($case) {
            'another member' => $this->budget->members()->attach($actor, ['role' => BudgetRole::Member]),
            'former member' => $this->budget->members()->detach($actor = $this->member),
            'outsider' => null,
        };
        Sanctum::actingAs($actor);

        $this->putJson("/api/v1/expenses/{$this->expense->id}", ['amount' => 9_999])->assertForbidden();

        expect($this->expense->fresh()->amount)->toBe(1_000);
    })->with(['another member', 'former member', 'outsider']);

    it('forbids changes once the budget is deleted', function () {
        Sanctum::actingAs($this->owner);
        $this->budget->delete();

        $this->putJson("/api/v1/expenses/{$this->expense->id}", ['amount' => 9_999])->assertForbidden();
    });
});

describe('destroy', function () {
    it('lets the author and the owner soft delete the expense', function (string $actor) {
        Sanctum::actingAs($this->{$actor});

        $this->deleteJson("/api/v1/expenses/{$this->expense->id}")->assertNoContent();

        expect($this->expense->fresh()->trashed())->toBeTrue();
    })->with(['member', 'owner']);

    it('forbids another member and an outsider', function (bool $is_member) {
        $actor = User::factory()->create();
        if ($is_member) {
            $this->budget->members()->attach($actor, ['role' => BudgetRole::Member]);
        }
        Sanctum::actingAs($actor);

        $this->deleteJson("/api/v1/expenses/{$this->expense->id}")->assertForbidden();

        expect($this->expense->fresh()->trashed())->toBeFalse();
    })->with(['another member' => true, 'outsider' => false]);
});
