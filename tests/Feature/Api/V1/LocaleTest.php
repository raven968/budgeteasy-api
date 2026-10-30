<?php

it('answers in spanish when the client asks for it', function () {
    $this->postJson('/api/v1/auth/login', [], ['Accept-Language' => 'es-MX,es;q=0.9'])
        ->assertUnprocessable()
        ->assertHeader('Content-Language', 'es')
        ->assertJsonPath('errors.email.0', 'El campo correo electrónico es obligatorio.');
});

it('answers in english when the client asks for it', function () {
    $this->postJson('/api/v1/auth/login', [], ['Accept-Language' => 'en-US,en;q=0.9'])
        ->assertUnprocessable()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('errors.email.0', 'The email field is required.');
});

it('falls back to spanish for unsupported languages', function () {
    $this->postJson('/api/v1/auth/login', [], ['Accept-Language' => 'fr-FR'])
        ->assertUnprocessable()
        ->assertHeader('Content-Language', 'es');
});
