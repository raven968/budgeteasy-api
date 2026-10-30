<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Semantic version of this backend. Bump it on every release together with
    | CHANGELOG.md and the git tag (vX.Y.Z).
    |
    */

    'api_version' => '0.2.0',

    /*
    |--------------------------------------------------------------------------
    | Minimum Mobile Version
    |--------------------------------------------------------------------------
    |
    | Oldest mobile app version allowed to use the API. The app compares its own
    | version against this value and forces an update when it is lower. It lives
    | in the environment so it can be raised without a deploy.
    |
    */

    'min_mobile_version' => env('MIN_MOBILE_VERSION', '0.1.0'),

];
