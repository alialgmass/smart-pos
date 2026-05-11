<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Facades\Auth;

class AuthenticateUserAction
{
    public function execute(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ], $remember);
    }
}
