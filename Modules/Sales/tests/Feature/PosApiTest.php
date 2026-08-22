<?php

namespace Modules\Sales\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Product;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosApiTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    #[Test]
    public function categories_endpoint_returns_tenant_categories_ordered(): void
    {
        Category::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Beverages',
            'sort_order' => 2,
        ]);

        Category::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Snacks',
            'sort_order' => 1,
        ]);

        Category::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'name' => 'Other Tenant',
            'sort_order' => 0,
        ]);

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $response = $this->getJson(route('api.pos.categories'));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonPath('0.name', 'Snacks');
        $response->assertJsonPath('1.name', 'Beverages');
    }

    #[Test]
    public function search_filters_products_by_category(): void
    {
        $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);
        $otherCategory = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cola',
            'category_id' => $category->id,
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Chips',
            'category_id' => $otherCategory->id,
        ]);

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $response = $this->getJson(route('api.pos.search', [
            'q' => '',
            'category_id' => $category->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.name', 'Cola');
        $response->assertJsonPath('0.category_id', $category->id);
    }

    #[Test]
    public function search_respects_limit_parameter(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Product::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name' => "Product {$i}",
            ]);
        }

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $response = $this->getJson(route('api.pos.search', ['limit' => 3]));

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    #[Test]
    public function search_includes_category_id_for_grid_filtering(): void
    {
        $category = Category::factory()->create(['tenant_id' => $this->tenant->id]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cola',
            'category_id' => $category->id,
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Water',
            'category_id' => null,
        ]);

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $response = $this->getJson(route('api.pos.search', ['q' => '']));

        $response->assertOk();

        $products = collect($response->json());
        $cola = $products->firstWhere('name', 'Cola');

        $this->assertSame($category->id, $cola['category_id']);
        $this->assertNull($products->firstWhere('name', 'Water')['category_id']);
    }

    #[Test]
    public function categories_endpoint_requires_authentication(): void
    {
        $response = $this->get(route('api.pos.categories'));

        $response->assertRedirect();
    }
}
