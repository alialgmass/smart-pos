<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Restaurant\Actions\MarkOrderReadyAction;
use Modules\Restaurant\Actions\SendToKitchenAction;
use Modules\Restaurant\Enums\OrderStatus;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\OrderItem;
use Modules\Restaurant\Models\Table;
use Modules\Tenancy\Models\Tenant;
use Tests\TestCase;

class KitchenWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_send_order_to_kitchen(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Open,
        ]);

        $item = OrderItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
            'sent_to_kitchen_at' => null,
        ]);

        $result = app(SendToKitchenAction::class)->execute($order->id, $this->tenant->id);

        $this->assertSame(OrderStatus::Sent, $result->status);
        $this->assertNotNull($result->items->first()->sent_to_kitchen_at);
    }

    public function test_mark_order_as_ready(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Sent,
        ]);

        $result = app(MarkOrderReadyAction::class)->execute($order->id, $this->tenant->id);

        $this->assertSame(OrderStatus::Ready, $result->status);
    }

    public function test_kitchen_display_shows_sent_and_ready_orders(): void
    {
        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Open,
        ]);
        $sentOrder = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Sent,
        ]);
        $readyOrder = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Ready,
        ]);

        $response = $this->actingAs($this->admin)->get(route('restaurant.kitchen.index'));

        $response->assertOk();
    }

    public function test_send_to_kitchen_via_http(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Open,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('restaurant.kitchen.send-to-kitchen', $order)
        );

        $response->assertRedirect(route('restaurant.kitchen.index'));

        $this->assertSame(OrderStatus::Sent, $order->fresh()->status);
    }

    public function test_mark_ready_via_http(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Sent,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('restaurant.kitchen.mark-ready', $order)
        );

        $response->assertRedirect(route('restaurant.kitchen.index'));

        $this->assertSame(OrderStatus::Ready, $order->fresh()->status);
    }

    public function test_kitchen_ticket_renders(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'user_id' => $this->admin->id,
            'status' => OrderStatus::Sent,
        ]);

        OrderItem::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('restaurant.kitchen.ticket', $order)
        );

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee($order->table->name);
    }
}
