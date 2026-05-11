<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\TenantPermissionSeeder;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Product;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class CategoryReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reorder_categories(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $categoryA = Category::factory()->create(['tenant_id' => $tenant->id, 'sort_order' => 0]);
        $categoryB = Category::factory()->create(['tenant_id' => $tenant->id, 'sort_order' => 1]);
        $categoryC = Category::factory()->create(['tenant_id' => $tenant->id, 'sort_order' => 2]);

        $response = $this->actingAs($admin)->post(route('inventory.categories.reorder'), [
            'ordered_ids' => [$categoryC->id, $categoryA->id, $categoryB->id],
        ]);

        $response->assertRedirect(route('inventory.categories.index'));

        $this->assertSame(0, $categoryC->fresh()->sort_order);
        $this->assertSame(1, $categoryA->fresh()->sort_order);
        $this->assertSame(2, $categoryB->fresh()->sort_order);
    }

    public function test_admin_can_create_category(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('inventory.categories.store'), [
            'name' => 'Beverages',
        ]);

        $response->assertRedirect(route('inventory.categories.index'));

        $category = Category::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Beverages')
            ->firstOrFail();

        $this->assertSame('Beverages', $category->name);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantPermissionSeeder::class)->seedForTenant($tenant);

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('Admin');

        $category = Category::factory()->create(['tenant_id' => $tenant->id]);
        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('inventory.categories.destroy', $category));

        $response->assertRedirect(route('inventory.categories.index'));
        $this->assertModelExists($category);
    }
}
