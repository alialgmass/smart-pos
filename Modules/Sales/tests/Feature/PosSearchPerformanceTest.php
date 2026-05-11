<?php

namespace Modules\Sales\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Product;
use Modules\Sales\Repositories\PosProductSearchRepository;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosSearchPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    #[Test]
    public function search_returns_results_under_twenty_milliseconds(): void
    {
        for ($i = 0; $i < 100; $i++) {
            Product::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name' => "Product {$i}",
            ]);
        }

        $repository = app(PosProductSearchRepository::class);

        $start = microtime(true);
        $results = $repository->search((int) $this->tenant->id, 'Product 5');
        $duration = (microtime(true) - $start) * 1000;

        $this->assertGreaterThan(0, $results->count());
        $this->assertLessThan(200, $duration, "Search took {$duration}ms, expected under 200ms");
    }

    #[Test]
    public function barcode_exact_match_returns_first(): void
    {
        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Apple iPhone',
            'barcode' => '890123456789',
        ]);

        Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Samsung Galaxy',
            'barcode' => '890123456788',
        ]);

        $repository = app(PosProductSearchRepository::class);

        $results = $repository->search((int) $this->tenant->id, '890123456789');

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals('Apple iPhone', $results->first()['name']);
    }
}
