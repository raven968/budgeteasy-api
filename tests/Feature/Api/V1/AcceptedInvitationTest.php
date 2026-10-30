<?php

use App\Enums\BudgetRole;
use App\Models\Budget;
use App\Models\BudgetInvitation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->budget = Budget::factory()->for($this->owner, 'owner')->create(['name' => 'Casa']);
    $this->invitation = BudgetInvitation::factory()->for($this->budget)->create(['code' => 'ABC234']);
    $this->guest = User::factory()->create();

    Sanctum::actingAs($this->guest);
    $this->withHeader('Accept-Language', 'es');
});

it('joins the budget as a member and consumes the code', function () {
    $this->postJson('/api/v1/invitations/accept', ['code' => ' abc234 '])
        ->assertOk()
        ->assertJsonPath('data.id', $this->budget->id)
        ->assertJsonPath('data.name', 'Casa')
        ->assertJsonPath('data.role', BudgetRole::Member->value);

    expect($this->budget->members()->find($this->guest->id)->pivot->role)->toBe(BudgetRole::Member)
        ->and($this->invitation->fresh())
        ->accepted_by->toBe($this->guest->id)
        ->accepted_at->not->toBeNull();
});

it('rejects codes that can not be accepted', function (Closure $setup, string $message) {
    $setup->call($this);

    $this->postJson('/api/v1/invitations/accept', ['code' => 'ABC234'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code' => $message]);

    expect($this->budget->hasMember($this->guest))->toBeFalse();
})->with([
    'unknown code' => [fn () => $this->invitation->update(['code' => 'ZZZ999']), 'no es válido'],
    'deleted budget' => [fn () => $this->budget->delete(), 'no es válido'],
    'expired code' => [fn () => $this->invitation->update(['expires_at' => now()->subMinute()]), 'expiró'],
    'used code' => [fn () => $this->invitation->forceFill(['accepted_by' => User::factory()->create()->id, 'accepted_at' => now()])->save(), 'ya fue utilizado'],
]);

it('rejects a user who is already a member', function () {
    Sanctum::actingAs($this->owner);

    $this->postJson('/api/v1/invitations/accept', ['code' => 'ABC234'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code' => 'Ya eres miembro de este presupuesto.']);

    expect($this->invitation->fresh()->accepted_at)->toBeNull();
});

it('requires a code', function () {
    $this->postJson('/api/v1/invitations/accept', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('throttles repeated attempts', function () {
    foreach (range(1, 10) as $attempt) {
        $this->postJson('/api/v1/invitations/accept', ['code' => 'WRONG1'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/invitations/accept', ['code' => 'ABC234'])->assertTooManyRequests();
});
