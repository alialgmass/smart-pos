<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Actions\ImportProductsAction;
use Modules\Inventory\Models\Product;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_preview_validates_invalid_rows(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $rows = [
            ['name' => '', 'price' => 20],
            ['name' => 'Product C', 'price' => 'invalid'],
        ];

        $response = $this->actingAs($admin)->postJson(route('inventory.products.import.preview'), [
            'rows' => $rows,
        ]);

        $response->assertUnprocessable();
    }

    public function test_import_preview_with_valid_rows(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $rows = [
            ['name' => 'Valid Product', 'price' => 10.99],
        ];

        $response = $this->actingAs($admin)->postJson(route('inventory.products.import.preview'), [
            'rows' => $rows,
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'valid');
        $response->assertJsonCount(0, 'errors');
    }

    public function test_import_action_preview_reports_errors(): void
    {
        $action = new ImportProductsAction;

        $rows = [
            ['name' => 'Good', 'price' => 10],
            ['name' => '', 'price' => 20],
            ['name' => 'Bad Price', 'price' => 'invalid'],
        ];

        $result = $action->preview($rows);

        $this->assertCount(1, $result['valid']);
        $this->assertCount(2, $result['errors']);
        $this->assertSame('Product name is required.', $result['errors'][0]['message']);
    }

    public function test_import_confirm_creates_products(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $rows = [
            ['name' => 'Imported Product', 'price' => 15.99, 'barcode' => 'IMP001'],
        ];

        $response = $this->actingAs($admin)->post(route('inventory.products.import.confirm'), [
            'rows' => $rows,
        ]);

        $response->assertRedirect(route('inventory.products.index'));

        $product = Product::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('barcode', 'IMP001')
            ->firstOrFail();

        $this->assertSame('Imported Product', $product->name);
    }
}
