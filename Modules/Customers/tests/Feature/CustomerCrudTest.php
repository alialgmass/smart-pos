<?php

namespace Modules\Customers\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customers\Models\Customer;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_customer(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::withoutGlobalScope('tenant')
            ->where('phone', '1234567890')
            ->firstOrFail();

        $this->assertSame($tenant->id, $customer->tenant_id);
        $this->assertSame('John Doe', $customer->name);
        $this->assertSame('1234567890', $customer->phone);
    }

    public function test_admin_can_update_customer(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->put(route('customers.update', $customer), [
            'name' => 'Jane Doe',
            'phone' => $customer->phone,
        ]);

        $response->assertRedirect(route('customers.index'));

        $this->assertSame('Jane Doe', $customer->fresh()->name);
    }

    public function test_admin_can_view_customer(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->get(route('customers.show', $customer));

        $response->assertOk();
    }

    public function test_customer_validation_fails_without_name(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'name' => '',
            'phone' => '1234567890',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_phone_is_unique_per_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        Customer::factory()->create(['tenant_id' => $tenant->id, 'phone' => '1234567890']);

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'name' => 'Another Customer',
            'phone' => '1234567890',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_customer_search_by_name(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        Customer::factory()->create(['tenant_id' => $tenant->id, 'name' => 'John Doe', 'phone' => '1111111111']);
        Customer::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Jane Smith', 'phone' => '2222222222']);

        $response = $this->actingAs($admin)->get(route('customers.search', ['q' => 'John']));

        $response->assertOk();
        $response->assertJsonCount(1, 'customers');
        $this->assertSame('John Doe', $response->json('customers.0.name'));
    }
}
