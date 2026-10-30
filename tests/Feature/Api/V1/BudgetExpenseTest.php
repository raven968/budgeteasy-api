<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');

    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->budget = Budget::factory()->for($this->owner, 'owner')->sharedWith($this->member)->create();
    $this->category = Category::factory()->for($this->owner)->create(['name' => 'Súper']);
});

describe('index', function () {
    it('lists the expenses of the current month, newest first', function () {
        Sanctum::actingAs($this->member);
        $older = Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'spent_on' => '2026-10-01']);
        $newer = Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'spent_on' => '2026-10-14']);
        Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'spent_on' => '2026-09-30']);
        Expense::factory()->create(['spent_on' => '2026-10-10']);

        $this->getJson("/api/v1/budgets/{$this->budget->id}/expenses")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.0.category.name', 'Súper')
            ->assertJsonPath('data.0.spent_on', '2026-10-14')
            ->assertJsonStructure([
                'data' => [['id', 'budget_id', 'amount', 'spent_on', 'note', 'category' => ['id', 'name', 'icon', 'color', 'is_deleted'], 'user' => ['id', 'name'], 'created_at', 'updated_at']],
                'links', 'meta' => ['current_page', 'per_page', 'total'],
            ]);
    });

    it('filters by month, category and user', function () {
        Sanctum::actingAs($this->owner);
        $other_category = Category::factory()->for($this->owner)->create();
        $match = Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'user_id' => $this->member->id, 'spent_on' => '2026-09-05']);
        Expense::factory()->for($this->budget)->create(['category_id' => $other_category->id, 'user_id' => $this->member->id, 'spent_on' => '2026-09-06']);
        Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'user_id' => $this->owner->id, 'spent_on' => '2026-09-07']);

        $this->getJson("/api/v1/budgets/{$this->budget->id}/expenses?month=2026-09&category_id={$this->category->id}&user_id={$this->member->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    });

    it('shows a deleted category on past expenses', function () {
        Sanctum::actingAs($this->owner);
        Expense::factory()->for($this->budget)->create(['category_id' => $this->category->id, 'spent_on' => '2026-10-02']);
        $this->category->delete();

        $this->getJson("/api/v1/budgets/{$this->budget->id}/expenses")
            ->assertOk()
            ->assertJsonPath('data.0.category.is_deleted', true);
    });

    it('rejects an invalid month', function () {
        Sanctum::actingAs($this->owner);

        $this->getJson("/api/v1/budgets/{$this->budget->id}/expenses?month=2026-13")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('month');
    });

    it('forbids a user who is not a member', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/budgets/{$this->budget->id}/expenses")->assertForbidden();
    });
});

describe('store', function () {
    it('lets a member register an expense in their name', function () {
        Sanctum::actingAs($this->member);

        $this->postJson("/api/v1/budgets/{$this->budget->id}/expenses", [
            'category_id' => $this->category->id,
            'amount' => 45_050,
            'spent_on' => '2026-10-14',
            'note' => 'Despensa semanal',
        ])
            ->assertCreated()
            ->assertJsonPath('data.amount', 45_050)
            ->assertJsonPath('data.user.id', $this->member->id)
            ->assertJsonPath('data.category.id', $this->category->id);

        expect($this->budget->expenses()->sole())
            ->user_id->toBe($this->member->id)
            ->note->toBe('Despensa semanal');
    });

    it('only accepts active categories of the budget owner', function (string $case) {
        Sanctum::actingAs($this->member);
        $category = match ($case) {
            'member category' => Category::factory()->for($this->member)->create(),
            'foreign category' => Category::factory()->create(),
            'deleted category' => tap(Category::factory()->for($this->owner)->create())->delete(),
        };

        $this->postJson("/api/v1/budgets/{$this->budget->id}/expenses", [
            'category_id' => $category->id,
            'amount' => 1_000,
            'spent_on' => '2026-10-14',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    })->with(['member category', 'foreign category', 'deleted category']);

    it('validates the fields', function (array $payload, string $field) {
        Sanctum::actingAs($this->owner);

        $this->postJson("/api/v1/budgets/{$this->budget->id}/expenses", array_merge([
            'category_id' => $this->category->id,
            'amount' => 1_000,
            'spent_on' => '2026-10-14',
        ], $payload))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    })->with([
        'zero amount' => [['amount' => 0], 'amount'],
        'decimal amount' => [['amount' => 10.5], 'amount'],
        'bad date' => [['spent_on' => '14/10/2026'], 'spent_on'],
        'long note' => [['note' => str_repeat('a', 256)], 'note'],
    ]);

    it('forbids a user who is not a member', function () {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/budgets/{$this->budget->id}/expenses", [
            'category_id' => $this->category->id,
            'amount' => 1_000,
            'spent_on' => '2026-10-14',
        ])->assertForbidden();

        expect($this->budget->expenses()->count())->toBe(0);
    });
});
