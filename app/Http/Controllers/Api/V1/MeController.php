<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1')]
#[Middleware('auth:sanctum')]
class MeController extends Controller
{
    #[Get('me', name: 'v1.me')]
    public function __invoke(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
