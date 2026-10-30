<?php

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();

    Sanctum::actingAs($this->user);
});

describe('index', function () {
    it('lists owned and shared budgets with the role of the user', function () {
        $owned = Budget::factory()->for($this->user, 'owner')->create(['name' => 'Casa']);
        $shared = Budget::factory()->sharedWith($this->user)->create(['name' => 'Vacaciones']);
        Budget::factory()->create(['name' => 'Ajeno']);

        $this->getJson('/api/v1/budgets')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $owned->id)
            ->assertJsonPath('data.0.role', BudgetRole::Owner->value)
            ->assertJsonPath('data.1.id', $shared->id)
            ->assertJsonPath('data.1.role', BudgetRole::Member->value)
            ->assertJsonPath('data.1.owner.id', $shared->owner_id)
            ->assertJsonStructure(['data' => [['id', 'name', 'amount', 'currency', 'role', 'owner' => ['id', 'name'], 'created_at', 'updated_at']]]);
    });

    it('omits deleted budgets', function () {
        Budget::factory()->for($this->user, 'owner')->create()->delete();

        $this->getJson('/api/v1/budgets')->assertOk()->assertJsonCount(0, 'data');
    });
});

describe('store', function () {
    it('creates a budget owned by the user', function () {
        $this->postJson('/api/v1/budgets', ['name' => 'Casa', 'amount' => 1_500_000, 'currency' => 'mxn'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Casa')
            ->assertJsonPath('data.amount', 1_500_000)
            ->assertJsonPath('data.currency', 'MXN')
            ->assertJsonPath('data.role', BudgetRole::Owner->value);

        $budget = $this->user->ownedBudgets()->sole();

        expect($budget->members()->sole()->pivot->role)->toBe(BudgetRole::Owner);
    });

    it('validates the fields', function (array $payload, string $field) {
        $this->postJson('/api/v1/budgets', array_merge(['name' => 'Casa', 'amount' => 100, 'currency' => 'MXN'], $payload))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'zero amount' => [['amount' => 0], 'amount'],
        'decimal amount' => [['amount' => 10.5], 'amount'],
        'amount too large' => [['amount' => 100_000_000_000], 'amount'],
        'unsupported currency' => [['currency' => 'XYZ'], 'currency'],
    ]);
});

describe('show', function () {
    it('shows a budget to its members', function () {
        $budget = Budget::factory()->sharedWith($this->user)->create();

        $this->getJson("/api/v1/budgets/{$budget->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $budget->id)
            ->assertJsonPath('data.role', BudgetRole::Member->value);
    });

    it('forbids a user who is not a member', function () {
        $budget = Budget::factory()->create();

        $this->getJson("/api/v1/budgets/{$budget->id}")->assertForbidden();
    });

    it('returns not found for a deleted budget', function () {
        $budget = Budget::factory()->for($this->user, 'owner')->create();
        $budget->delete();

        $this->getJson("/api/v1/budgets/{$budget->id}")->assertNotFound();
    });
});

describe('update', function () {
    it('lets the owner update the budget', function () {
        $budget = Budget::factory()->for($this->user, 'owner')->create();

        $this->putJson("/api/v1/budgets/{$budget->id}", ['amount' => 2_000_000, 'currency' => 'USD'])
            ->assertOk()
            ->assertJsonPath('data.amount', 2_000_000)
            ->assertJsonPath('data.currency', 'USD');
    });

    it('forbids members and outsiders', function (string $relation) {
        $budget = $relation === 'member'
            ? Budget::factory()->sharedWith($this->user)->create(['name' => 'Original'])
            : Budget::factory()->create(['name' => 'Original']);

        $this->putJson("/api/v1/budgets/{$budget->id}", ['name' => 'Cambiado'])->assertForbidden();

        expect($budget->fresh()->name)->toBe('Original');
    })->with(['member', 'outsider']);
});

describe('destroy', function () {
    it('lets the owner soft delete the budget', function () {
        $budget = Budget::factory()->for($this->user, 'owner')->create();

        $this->deleteJson("/api/v1/budgets/{$budget->id}")->assertNoContent();

        expect($budget->fresh()->trashed())->toBeTrue();
    });

    it('forbids members and outsiders', function (string $relation) {
        $budget = $relation === 'member'
            ? Budget::factory()->sharedWith($this->user)->create()
            : Budget::factory()->create();

        $this->deleteJson("/api/v1/budgets/{$budget->id}")->assertForbidden();

        expect($budget->fresh()->trashed())->toBeFalse();
    })->with(['member', 'outsider']);
});
