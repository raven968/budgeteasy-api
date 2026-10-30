<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();

    Sanctum::actingAs($this->user);
});

it('gives members the active categories of the budget owner', function () {
    $budget = Budget::factory()->sharedWith($this->user)->create();
    Category::factory()->for($budget->owner)->create(['name' => 'Súper']);
    Category::factory()->for($budget->owner)->create(['name' => 'Borrada'])->delete();
    Category::factory()->for($this->user)->create(['name' => 'Mía']);

    $this->getJson("/api/v1/budgets/{$budget->id}/categories")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Súper');
});

it('forbids a user who is not a member', function () {
    $budget = Budget::factory()->create();

    $this->getJson("/api/v1/budgets/{$budget->id}/categories")->assertForbidden();
});
