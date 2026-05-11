<?php

namespace Modules\Customers\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Models\Product;
use Modules\Sales\Actions\ProcessReturnAction;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class ProductReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_restores_stock(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create([
            'tenant_id' => $tenant->id,
            'stock_qty' => 10,
        ]);

        $sale = Sale::factory()->create(['tenant_id' => $tenant->id]);

        $saleItem = SaleItem::factory()->create([
            'tenant_id' => $tenant->id,
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 3,
            'price' => 10.00,
        ]);

        $this->actingAs($admin);

        $action = app(ProcessReturnAction::class);
        $action->execute($sale, [
            ['item_id' => $saleItem->id, 'qty' => 2],
        ]);

        $product->refresh();
        $this->assertSame(12.0, (float) $product->stock_qty);
    }

    public function test_partial_return_restocks_correct_qty(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create([
            'tenant_id' => $tenant->id,
            'stock_qty' => 5,
        ]);

        $sale = Sale::factory()->create(['tenant_id' => $tenant->id]);

        $saleItem = SaleItem::factory()->create([
            'tenant_id' => $tenant->id,
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 3,
            'price' => 10.00,
        ]);

        $this->actingAs($admin);

        $action = app(ProcessReturnAction::class);
        $action->execute($sale, [
            ['item_id' => $saleItem->id, 'qty' => 1],
        ]);

        $product->refresh();
        $this->assertSame(6.0, (float) $product->stock_qty);
    }
}
