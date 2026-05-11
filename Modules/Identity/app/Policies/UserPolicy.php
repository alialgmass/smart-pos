<?php

namespace Modules\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->tenant_id === $model->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->tenant_id === $model->tenant_id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->id !== $model->id && $user->tenant_id === $model->tenant_id;
    }

    public function toggleActive(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->id !== $model->id && $user->tenant_id === $model->tenant_id;
    }
}
