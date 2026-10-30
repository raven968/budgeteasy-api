<?php

use App\Models\User;

/**
 * @return array<string, string>
 */
function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Aron Jimenez',
        'email' => 'aron@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'device_name' => 'iPhone de Aron',
    ], $overrides);
}

it('registers a user and returns a token', function () {
    $this->postJson('/api/v1/auth/register', registerPayload())
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['token', 'token_type', 'user' => ['id', 'name', 'email', 'created_at']],
        ])
        ->assertJsonPath('data.user.email', 'aron@example.com');

    $user = User::firstWhere('email', 'aron@example.com');

    expect($user)->not->toBeNull()
        ->and($user->tokens()->sole()->name)->toBe('iPhone de Aron');
});

it('normalizes the email to lowercase', function () {
    $this->postJson('/api/v1/auth/register', registerPayload(['email' => '  Aron@Example.COM ']))
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'aron@example.com');
});

it('rejects an email that is already registered regardless of case', function () {
    User::factory()->create(['email' => 'aron@example.com']);

    $this->postJson('/api/v1/auth/register', registerPayload(['email' => 'ARON@example.com']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('requires all fields', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'device_name']);
});

it('requires the password confirmation to match', function () {
    $this->postJson('/api/v1/auth/register', registerPayload(['password_confirmation' => 'different123']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('requires the password to contain letters and numbers', function () {
    $this->postJson('/api/v1/auth/register', registerPayload([
        'password' => 'onlyletters',
        'password_confirmation' => 'onlyletters',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});
