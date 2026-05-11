<?php

namespace Modules\Sales\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    #[Test]
    public function receipt_view_renders_sale_details(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $sale = Sale::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-1-TEST',
            'subtotal' => 100.00,
            'tax_amount' => 14.00,
            'total' => 114.00,
            'paid_amount' => 114.00,
            'change_amount' => 0,
        ]);

        SaleItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'sale_id' => $sale->id,
            'name' => 'Test Item',
            'price' => 50.00,
            'qty' => 2,
            'total' => 100.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('sales.receipts.show', $sale->id));

        $response->assertOk();
        $response->assertSee('INV-1-TEST');
        $response->assertSee('Test Item');
        $response->assertSee('100.00');
        $response->assertSee('114.00');
    }

    #[Test]
    public function receipt_requires_authentication(): void
    {
        $sale = Sale::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->get(route('sales.receipts.show', $sale->id));

        $response->assertRedirect(route('login'));
    }
}
