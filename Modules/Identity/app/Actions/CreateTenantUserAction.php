<?php

namespace Modules\Identity\Actions;

use App\Models\User;
use Illuminate\Support\Str;

class CreateTenantUserAction
{
    /**
     * @param  array{name:string,email:string,password?:string|null,role:string}  $data
     */
    public function execute(User $actor, array $data): User
    {
        $user = User::create([
            'tenant_id' => $actor->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?: Str::password(12),
            'is_active' => true,
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }
}
