<?php

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->budget = Budget::factory()->for($this->owner, 'owner')->sharedWith($this->member)->create();
});

describe('index', function () {
    it('lists the members with their role, owner first', function () {
        Sanctum::actingAs($this->member);

        $this->getJson("/api/v1/budgets/{$this->budget->id}/members")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $this->owner->id)
            ->assertJsonPath('data.0.role', BudgetRole::Owner->value)
            ->assertJsonPath('data.1.id', $this->member->id)
            ->assertJsonPath('data.1.role', BudgetRole::Member->value)
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'role', 'joined_at']]]);
    });

    it('forbids a user who is not a member', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/budgets/{$this->budget->id}/members")->assertForbidden();
    });
});

describe('destroy', function () {
    it('lets the owner remove a member', function () {
        Sanctum::actingAs($this->owner);

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$this->member->id}")->assertNoContent();

        expect($this->budget->hasMember($this->member))->toBeFalse();
    });

    it('lets a member leave the budget', function () {
        Sanctum::actingAs($this->member);

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$this->member->id}")->assertNoContent();

        expect($this->budget->hasMember($this->member))->toBeFalse();
    });

    it('forbids a member from removing another member', function () {
        $other_member = User::factory()->create();
        $this->budget->members()->attach($other_member, ['role' => BudgetRole::Member]);
        Sanctum::actingAs($this->member);

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$other_member->id}")->assertForbidden();

        expect($this->budget->hasMember($other_member))->toBeTrue();
    });

    it('never removes the owner', function (string $actor) {
        Sanctum::actingAs($this->{$actor});

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$this->owner->id}")->assertForbidden();

        expect($this->budget->hasMember($this->owner))->toBeTrue();
    })->with(['owner', 'member']);

    it('forbids a user who is not a member', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$this->member->id}")->assertForbidden();

        expect($this->budget->hasMember($this->member))->toBeTrue();
    });

    it('returns not found when the user is not a member of the budget', function () {
        Sanctum::actingAs($this->owner);
        $stranger = User::factory()->create();

        $this->deleteJson("/api/v1/budgets/{$this->budget->id}/members/{$stranger->id}")->assertNotFound();
    });
});
