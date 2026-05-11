<?php

namespace Modules\Sales\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Sales\Actions\ProcessSaleAction;
use Modules\Sales\Enums\SaleStatus;
use Modules\Sales\Events\SaleCompleted;
use Modules\Sales\Models\Sale;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessSaleActionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    #[Test]
    public function it_creates_a_sale_with_items(): void
    {
        Event::fake();

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $data = [
            'items' => [
                [
                    'product_id' => null,
                    'name' => 'Test Product',
                    'price' => 50.00,
                    'cost' => 30.00,
                    'qty' => 2,
                    'discount' => 0,
                    'tax_amount' => 14.00,
                ],
            ],
            'payment_method' => 1,
            'subtotal' => 100.00,
            'discount_amount' => 0,
            'tax_amount' => 14.00,
            'total' => 114.00,
            'paid_amount' => 114.00,
            'change_amount' => 0,
        ];

        $action = app(ProcessSaleAction::class);
        $sale = $action->execute($data, $user);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals($this->tenant->id, $sale->tenant_id);
        $this->assertEquals($user->id, $sale->user_id);
        $this->assertEquals(100.00, $sale->subtotal);
        $this->assertEquals(114.00, $sale->total);
        $this->assertEquals(SaleStatus::Completed, $sale->status);
        $this->assertStringStartsWith('INV-'.$this->tenant->id.'-', $sale->invoice_number);

        $this->assertCount(1, $sale->items);
        $this->assertEquals('Test Product', $sale->items->first()->name);
        $this->assertEquals(50.00, $sale->items->first()->price);
        $this->assertEquals(2, $sale->items->first()->qty);

        Event::assertDispatched(SaleCompleted::class);
    }

    #[Test]
    public function it_generates_unique_invoice_number(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $data = [
            'items' => [
                [
                    'name' => 'Item A',
                    'price' => 10.00,
                    'cost' => 5.00,
                    'qty' => 1,
                    'discount' => 0,
                    'tax_amount' => 1.40,
                ],
            ],
            'payment_method' => 1,
            'subtotal' => 10.00,
            'discount_amount' => 0,
            'tax_amount' => 1.40,
            'total' => 11.40,
            'paid_amount' => 11.40,
            'change_amount' => 0,
        ];

        $action = app(ProcessSaleAction::class);
        $sale1 = $action->execute($data, $user);
        $sale2 = $action->execute($data, $user);

        $this->assertNotSame($sale1->invoice_number, $sale2->invoice_number);
    }
}
