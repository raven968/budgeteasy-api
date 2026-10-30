<?php

use App\Models\Budget;
use App\Models\BudgetInvitation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');

    $this->owner = User::factory()->create();
    $this->member = User::factory()->create();
    $this->budget = Budget::factory()->for($this->owner, 'owner')->sharedWith($this->member)->create();
});

it('lets the owner generate a code that expires in 48 hours', function () {
    Sanctum::actingAs($this->owner);

    $response = $this->postJson("/api/v1/budgets/{$this->budget->id}/invitations")
        ->assertCreated()
        ->assertJsonPath('data.budget_id', $this->budget->id)
        ->assertJsonPath('data.expires_at', '2026-10-17T12:00:00.000000Z');

    expect($response->json('data.code'))->toMatch('/^[ABCDEFGHJKMNPQRSTUVWXYZ23456789]{6}$/');

    expect($this->budget->invitations()->sole())
        ->code->toBe($response->json('data.code'))
        ->created_by->toBe($this->owner->id);
});

it('expires the previous pending code when generating a new one', function () {
    Sanctum::actingAs($this->owner);
    $previous = BudgetInvitation::factory()->for($this->budget)->create();
    $accepted = BudgetInvitation::factory()->for($this->budget)->acceptedBy($this->member)->create();

    $this->postJson("/api/v1/budgets/{$this->budget->id}/invitations")->assertCreated();

    expect($previous->fresh()->expires_at->isFuture())->toBeFalse()
        ->and($accepted->fresh()->expires_at->isFuture())->toBeTrue()
        ->and($this->budget->invitations()->pending()->count())->toBe(1);
});

it('forbids members and outsiders', function (bool $is_member) {
    Sanctum::actingAs($is_member ? $this->member : User::factory()->create());

    $this->postJson("/api/v1/budgets/{$this->budget->id}/invitations")->assertForbidden();

    expect($this->budget->invitations()->count())->toBe(0);
})->with(['member' => true, 'outsider' => false]);
