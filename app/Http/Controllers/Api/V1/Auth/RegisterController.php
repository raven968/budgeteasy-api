<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthTokenResource;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/auth')]
class RegisterController extends Controller
{
    #[Post('register', name: 'v1.auth.register', middleware: 'throttle:auth')]
    public function __invoke(RegisterRequest $request, RegisterUserAction $register_user): JsonResponse
    {
        $new_access_token = $register_user->handle($request->validated());

        return AuthTokenResource::make($new_access_token)
            ->response()
            ->setStatusCode(201);
    }
}
