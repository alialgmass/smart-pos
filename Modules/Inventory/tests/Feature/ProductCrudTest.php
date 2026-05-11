<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Models\Product;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('inventory.products.store'), [
            'name' => 'Test Product',
            'price' => 29.99,
            'barcode' => '1234567890',
        ]);

        $response->assertRedirect(route('inventory.products.index'));

        $product = Product::withoutGlobalScope('tenant')
            ->where('barcode', '1234567890')
            ->firstOrFail();

        $this->assertSame($tenant->id, $product->tenant_id);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame(29.99, (float) $product->price);
    }

    public function test_admin_can_update_product(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->put(route('inventory.products.update', $product), [
            'name' => 'Updated Product',
            'price' => 49.99,
        ]);

        $response->assertRedirect(route('inventory.products.index'));

        $this->assertSame('Updated Product', $product->fresh()->name);
        $this->assertSame(49.99, (float) $product->fresh()->price);
    }

    public function test_admin_can_delete_product(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->delete(route('inventory.products.destroy', $product));

        $response->assertRedirect(route('inventory.products.index'));

        $this->assertSoftDeleted($product);
    }

    public function test_product_validation_fails_without_name(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('inventory.products.store'), [
            'name' => '',
            'price' => 10,
        ]);

        $response->assertSessionHasErrors(['name']);
    }
}
