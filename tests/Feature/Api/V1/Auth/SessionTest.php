<?php

use App\Models\User;

it('logs in with valid credentials and returns a token', function () {
    $user = User::factory()->create(['email' => 'aron@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'Aron@Example.com',
        'password' => 'password',
        'device_name' => 'Pixel',
    ])
        ->assertOk()
        ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['id', 'name', 'email']]])
        ->assertJsonPath('data.user.id', $user->id);

    expect($user->tokens()->sole()->name)->toBe('Pixel');
});

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'aron@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'aron@example.com',
        'password' => 'wrong-password',
        'device_name' => 'Pixel',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects an unknown email', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
        'device_name' => 'Pixel',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('throttles repeated login attempts', function () {
    User::factory()->create(['email' => 'aron@example.com']);

    $payload = [
        'email' => 'aron@example.com',
        'password' => 'wrong-password',
        'device_name' => 'Pixel',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', $payload)->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', $payload)->assertTooManyRequests();
});

it('logs out by revoking only the current token', function () {
    $user = User::factory()->create();
    $current_token = $user->createToken('iPhone')->plainTextToken;
    $user->createToken('iPad');

    $this->withToken($current_token)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect($user->tokens()->pluck('name')->all())->toBe(['iPad']);
});
