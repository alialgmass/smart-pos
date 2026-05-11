<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_purchase_order(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create([
            'tenant_id' => $tenant->id,
            'stock_qty' => 10,
        ]);

        $response = $this->actingAs($admin)->post(route('inventory.purchase-orders.store'), [
            'supplier_name' => 'Test Supplier',
            'notes' => 'Test order',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 5,
                    'unit_cost' => 12.50,
                ],
            ],
        ]);

        $response->assertRedirect();

        $purchaseOrder = PurchaseOrder::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $this->assertSame('Test Supplier', $purchaseOrder->supplier_name);
        $this->assertSame(62.50, (float) $purchaseOrder->total_cost);
        $this->assertCount(1, $purchaseOrder->items);

        $item = $purchaseOrder->items->first();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame(5.0, (float) $item->qty);
    }

    public function test_purchase_order_requires_items(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('inventory.purchase-orders.store'), [
            'supplier_name' => 'Supplier',
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['items']);
    }
}
