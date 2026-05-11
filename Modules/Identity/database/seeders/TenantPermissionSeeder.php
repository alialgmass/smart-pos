<?php

namespace Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Tenancy\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally empty. Use seedForTenant() for tenant-specific setup.
    }

    public function seedForTenant(Tenant $tenant): void
    {
        if (! Schema::hasTable(config('permission.table_names.permissions', 'permissions'))) {
            return;
        }

        if (! Schema::hasTable(config('permission.table_names.roles', 'roles'))) {
            return;
        }

        $permissions = [
            'settings.manage',
            'products.manage',
            'customers.manage',
            'pos.discount',
            'pos.refund',
            'reports.profit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (['Admin', 'Manager', 'Cashier'] as $roleName) {
            Role::findOrCreate($roleName)->syncPermissions(match ($roleName) {
                'Admin' => $permissions,
                'Manager' => array_diff($permissions, ['settings.manage']),
                default => [],
            });
        }
    }
}
