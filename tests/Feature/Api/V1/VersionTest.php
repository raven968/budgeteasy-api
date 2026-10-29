<?php

it('returns the api version and the minimum mobile version', function () {
    config(['versioning.min_mobile_version' => '1.2.0']);

    $this->getJson('/api/v1/version')
        ->assertOk()
        ->assertExactJson([
            'api_version' => config('versioning.api_version'),
            'min_mobile_version' => '1.2.0',
        ]);
});
