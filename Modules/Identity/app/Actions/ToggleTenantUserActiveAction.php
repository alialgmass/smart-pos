<?php

namespace Modules\Identity\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ToggleTenantUserActiveAction
{
    public function execute(User $actor, User $user): User
    {
        if ($actor->is($user)) {
            throw ValidationException::withMessages([
                'user' => __('You cannot disable your own account.'),
            ]);
        }

        $user->forceFill([
            'is_active' => ! $user->is_active,
        ])->save();

        return $user;
    }
}
