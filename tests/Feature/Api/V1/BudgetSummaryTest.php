<?php

use App\Models\Budget;
use App\Models\BudgetCategoryLimit;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');

    $this->owner = User::factory()->create(['name' => 'Aron']);
    $this->member = User::factory()->create(['name' => 'Esposa']);
    $this->budget = Budget::factory()
        ->for($this->owner, 'owner')
        ->sharedWith($this->member)
        ->create(['amount' => 1_000_000, 'currency' => 'MXN']);
    $this->super = Category::factory()->for($this->owner)->create(['name' => 'Súper']);
    $this->gas = Category::factory()->for($this->owner)->create(['name' => 'Gasolina']);
    $this->unused = Category::factory()->for($this->owner)->create(['name' => 'Ropa']);

    Sanctum::actingAs($this->member);
});

function spend(Budget $budget, Category $category, User $user, int $amount, string $spent_on = '2026-10-10'): Expense
{
    return Expense::factory()->for($budget)->create([
        'category_id' => $category->id,
        'user_id' => $user->id,
        'amount' => $amount,
        'spent_on' => $spent_on,
    ]);
}

it('summarizes the spending of the current month', function () {
    spend($this->budget, $this->super, $this->owner, 300_000);
    spend($this->budget, $this->super, $this->member, 100_000);
    spend($this->budget, $this->gas, $this->member, 50_000);
    spend($this->budget, $this->gas, $this->member, 999_999, '2026-09-30');
    spend($this->budget, $this->gas, $this->owner, 888_888)->delete();
    BudgetCategoryLimit::factory()->for($this->budget)->create(['category_id' => $this->super->id, 'amount' => 500_000]);

    $this->getJson("/api/v1/budgets/{$this->budget->id}/summary")
        ->assertOk()
        ->assertJsonPath('data.month', '2026-10')
        ->assertJsonPath('data.currency', 'MXN')
        ->assertJsonPath('data.budget_amount', 1_000_000)
        ->assertJsonPath('data.spent_amount', 450_000)
        ->assertJsonPath('data.remaining_amount', 550_000)
        ->assertJsonPath('data.spent_percentage', 45)
        ->assertJsonCount(3, 'data.categories')
        ->assertJsonPath('data.categories.0.category.name', 'Súper')
        ->assertJsonPath('data.categories.0.spent_amount', 400_000)
        ->assertJsonPath('data.categories.0.expenses_count', 2)
        ->assertJsonPath('data.categories.0.limit_amount', 500_000)
        ->assertJsonPath('data.categories.1.category.name', 'Gasolina')
        ->assertJsonPath('data.categories.1.spent_amount', 50_000)
        ->assertJsonPath('data.categories.1.limit_amount', null)
        ->assertJsonPath('data.categories.2.category.name', 'Ropa')
        ->assertJsonPath('data.categories.2.spent_amount', 0)
        ->assertJsonPath('data.members.0.user.name', 'Aron')
        ->assertJsonPath('data.members.0.spent_amount', 300_000)
        ->assertJsonPath('data.members.1.user.name', 'Esposa')
        ->assertJsonPath('data.members.1.spent_amount', 150_000);
});

it('summarizes the requested month', function () {
    spend($this->budget, $this->gas, $this->member, 200_000, '2026-09-30');

    $this->getJson("/api/v1/budgets/{$this->budget->id}/summary?month=2026-09")
        ->assertOk()
        ->assertJsonPath('data.month', '2026-09')
        ->assertJsonPath('data.spent_amount', 200_000);
});

it('reports overspending as a negative remaining amount', function () {
    spend($this->budget, $this->super, $this->owner, 1_250_000);

    $this->getJson("/api/v1/budgets/{$this->budget->id}/summary")
        ->assertJsonPath('data.remaining_amount', -250_000)
        ->assertJsonPath('data.spent_percentage', 125);
});

it('keeps deleted categories, former members and deleted accounts that spent in the month', function () {
    $former_member = User::factory()->create(['name' => 'Ex']);
    $deleted_account = User::factory()->create();
    spend($this->budget, $this->super, $former_member, 10_000);
    spend($this->budget, $this->super, $deleted_account, 20_000);
    spend($this->budget, $this->gas, $this->owner, 30_000);
    $this->gas->delete();
    $deleted_account->delete();

    $response = $this->getJson("/api/v1/budgets/{$this->budget->id}/summary")->assertOk();

    expect(collect($response->json('data.categories'))->firstWhere('category.name', 'Gasolina'))
        ->category->is_deleted->toBeTrue()
        ->spent_amount->toBe(30_000);

    expect(collect($response->json('data.members'))->pluck('spent_amount', 'user.name')->all())
        ->toMatchArray(['Aron' => 30_000, 'Ex' => 10_000, 'Esposa' => 0])
        ->and(collect($response->json('data.members'))->firstWhere('user', null)['spent_amount'])->toBe(20_000);
});

it('omits deleted categories without spending in the month', function () {
    $this->unused->delete();

    $this->getJson("/api/v1/budgets/{$this->budget->id}/summary")
        ->assertJsonCount(2, 'data.categories');
});

it('forbids a user who is not a member', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/v1/budgets/{$this->budget->id}/summary")->assertForbidden();
});
