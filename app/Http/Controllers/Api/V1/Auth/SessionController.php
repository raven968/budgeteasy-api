<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthTokenResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/auth')]
class SessionController extends Controller
{
    /**
     * Log in and get a token for the device.
     *
     * @unauthenticated
     */
    #[Post('login', name: 'v1.auth.login', middleware: 'throttle:auth')]
    public function store(LoginRequest $request, LoginAction $login): AuthTokenResource
    {
        $new_access_token = $login->handle(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name'),
        );

        return AuthTokenResource::make($new_access_token);
    }

    #[Post('logout', name: 'v1.auth.logout', middleware: 'auth:sanctum')]
    public function destroy(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
