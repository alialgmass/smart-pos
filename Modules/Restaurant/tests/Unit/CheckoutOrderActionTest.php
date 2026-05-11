<?php

namespace Modules\Restaurant\Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Restaurant\Actions\CheckoutOrderAction;
use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\Table;
use Modules\Tenancy\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    #[Test]
    public function it_marks_order_as_paid(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id, 'status' => TableStatus::Occupied]);
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => OrderStatus::Ready,
        ]);

        $action = app(CheckoutOrderAction::class);
        $result = $action->execute($order->id, $this->tenant->id);

        $this->assertSame(OrderStatus::Paid, $result->status);
    }

    #[Test]
    public function it_frees_the_table_after_checkout(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id, 'status' => TableStatus::Occupied]);
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => OrderStatus::Ready,
        ]);

        $action = app(CheckoutOrderAction::class);
        $action->execute($order->id, $this->tenant->id);

        $this->assertSame(TableStatus::Available, $table->fresh()->status);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_order(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $action = app(CheckoutOrderAction::class);
        $action->execute(999, $this->tenant->id);
    }
}
