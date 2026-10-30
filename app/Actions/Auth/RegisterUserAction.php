<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\NewAccessToken;

class RegisterUserAction
{
    /**
     * Create a new user and issue an API token for the given device.
     *
     * @param  array{name: string, email: string, password: string, device_name: string}  $data
     */
    public function handle(array $data): NewAccessToken
    {
        return DB::transaction(function () use ($data): NewAccessToken {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            return $user->createToken($data['device_name']);
        });
    }
}
