<?php

namespace Modules\Identity\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class UserActiveToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_disable_and_enable_employee_without_deleting_history(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $cashier = User::factory()->create(['tenant_id' => $tenant->id]);
        $cashier->assignRole('Cashier');

        $disableResponse = $this->actingAs($admin)->patch(route('users.toggle-active', $cashier));

        $disableResponse->assertRedirect(route('users.index'));
        $this->assertFalse($cashier->fresh()->is_active);
        $this->assertModelExists($cashier);

        $this->actingAs($cashier->fresh())->get(route('dashboard'))
            ->assertRedirect('/login');
        $this->assertGuest();

        $enableResponse = $this->actingAs($admin)->patch(route('users.toggle-active', $cashier));

        $enableResponse->assertRedirect(route('users.index'));
        $this->assertTrue($cashier->fresh()->is_active);
    }

    public function test_admin_cannot_disable_their_own_account(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->patchJson(route('users.toggle-active', $admin));

        $response->assertForbidden();
        $this->assertTrue($admin->fresh()->is_active);
    }
}
