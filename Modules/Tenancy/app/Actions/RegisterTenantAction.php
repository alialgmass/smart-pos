<?php

namespace Modules\Tenancy\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Tenancy\DTOs\RegisterTenantData;
use Modules\Tenancy\Models\Tenant;

class RegisterTenantAction
{
    public function execute(RegisterTenantData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $tenant = Tenant::create([
                'name' => $data->storeName,
                'settings' => [],
                'plan_id' => null,
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data->ownerName,
                'email' => $data->email,
                'password' => $data->password,
                'is_active' => true,
            ]);

            $user->sendEmailVerificationNotification();

            app(TenantPermissionSeeder::class)->seedForTenant($tenant);

            $user->assignRole('Admin');

            return $user;
        });
    }
}
