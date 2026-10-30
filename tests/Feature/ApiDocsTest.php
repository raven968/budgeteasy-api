<?php

it('hides the API documentation outside the local environment', function (string $uri) {
    $this->get($uri)->assertForbidden();
})->with(['/docs/api', '/docs/api.json']);

it('serves the API documentation locally', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->getJson('/docs/api.json')
        ->assertOk()
        ->assertJsonPath('info.title', 'BudgetEasy API')
        ->assertJsonPath('info.version', config('versioning.api_version'));
});
