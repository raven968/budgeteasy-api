<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class LoginAction
{
    /**
     * Verify the credentials and issue an API token for the given device.
     *
     * @throws ValidationException
     */
    public function handle(string $email, string $password, string $device_name): NewAccessToken
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $user->createToken($device_name);
    }
}
