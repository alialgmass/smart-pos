<?php

namespace Modules\Identity\Actions;

use App\Models\User;

class UpdateTenantUserAction
{
    /**
     * @param  array{name:string,email:string,password?:string|null,role:string}  $data
     */
    public function execute(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return $user;
    }
}
