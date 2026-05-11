<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Restaurant\Actions\MarkOrderReadyAction;
use Modules\Restaurant\Actions\SendToKitchenAction;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Repositories\OrderRepository;

class KitchenController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly SendToKitchenAction $sendToKitchen,
        private readonly MarkOrderReadyAction $markReady,
    ) {}

    public function index(): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $orders = $this->orders->getKitchenOrders($tenantId);

        return Inertia::render('restaurant::Kitchen/Index', [
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'notes' => $order->notes,
                'created_at' => $order->created_at,
                'table' => $order->table ? ['id' => $order->table->id, 'name' => $order->table->name] : null,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'qty' => $item->qty,
                    'notes' => $item->notes,
                    'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                ]),
            ]),
        ]);
    }

    public function sendToKitchen(Order $order)
    {
        $this->sendToKitchen->execute($order->id, auth()->user()->tenant_id);

        return redirect()->route('restaurant.kitchen.index');
    }

    public function markReady(Order $order)
    {
        $this->markReady->execute($order->id, auth()->user()->tenant_id);

        return redirect()->route('restaurant.kitchen.index');
    }

    public function ticket(Order $order)
    {
        $tenantId = auth()->user()->tenant_id;

        $order = $this->orders->findWithItems($order->id, $tenantId);

        return view('restaurant::kitchen-ticket', [
            'order' => $order,
        ]);
    }
}
