<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1')]
class VersionController extends Controller
{
    /**
     * API version and the minimum mobile app version allowed.
     *
     * @unauthenticated
     */
    #[Get('version', name: 'v1.version')]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'api_version' => config('versioning.api_version'),
            'min_mobile_version' => config('versioning.min_mobile_version'),
        ]);
    }
}
