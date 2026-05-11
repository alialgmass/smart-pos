<?php

namespace Modules\Identity\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_tenant_users(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $createResponse = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Cashier User',
            'email' => 'cashier@example.com',
            'password' => 'password',
            'role' => 'Cashier',
        ]);

        $createResponse->assertRedirect(route('users.index'));

        $cashier = User::withoutGlobalScope('tenant')
            ->where('email', 'cashier@example.com')
            ->firstOrFail();

        $this->assertSame($tenant->id, $cashier->tenant_id);
        $this->assertTrue($cashier->hasRole('Cashier'));

        $updateResponse = $this->actingAs($admin)->put(route('users.update', $cashier), [
            'name' => 'Shift Manager',
            'email' => 'manager@example.com',
            'password' => null,
            'role' => 'Manager',
        ]);

        $updateResponse->assertRedirect(route('users.index'));
        $this->assertSame('Shift Manager', $cashier->fresh()->name);
        $this->assertTrue($cashier->fresh()->hasRole('Manager'));

        $deleteResponse = $this->actingAs($admin)->delete(route('users.destroy', $cashier));

        $deleteResponse->assertRedirect(route('users.index'));
        $this->assertModelMissing($cashier);
    }

    public function test_cashier_cannot_access_user_management(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $cashier = User::factory()->create(['tenant_id' => $tenant->id]);
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->getJson(route('users.index'));

        $response->assertForbidden();
    }
}
