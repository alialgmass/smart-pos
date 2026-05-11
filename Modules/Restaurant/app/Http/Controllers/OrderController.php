<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Restaurant\Actions\CheckoutOrderAction;
use Modules\Restaurant\Actions\OpenOrderAction;
use Modules\Restaurant\Actions\SendToKitchenAction;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Repositories\OrderRepository;
use Modules\Restaurant\Repositories\TableRepository;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly TableRepository $tables,
        private readonly OpenOrderAction $openOrder,
        private readonly SendToKitchenAction $sendToKitchen,
        private readonly CheckoutOrderAction $checkoutOrder,
    ) {}

    public function index(): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $orders = $this->orders->paginateForTenant($tenantId);

        return Inertia::render('restaurant::Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $tenantId = auth()->user()->tenant_id;

        $order = $this->orders->findWithItems($order->id, $tenantId);

        return Inertia::render('restaurant::Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'notes' => $order->notes,
                'created_at' => $order->created_at,
                'table' => $order->table ? ['id' => $order->table->id, 'name' => $order->table->name] : null,
                'user' => $order->user ? ['id' => $order->user->id, 'name' => $order->user->name] : null,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'qty' => $item->qty,
                    'notes' => $item->notes,
                    'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                ]),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'table_id' => ['required', 'integer', 'exists:restaurant_tables,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->openOrder->execute(
            tableId: $data['table_id'],
            user: auth()->user(),
            items: $data['items'],
            notes: $data['notes'] ?? null,
        );

        return redirect()->route('restaurant.orders.show', $order);
    }

    public function sendToKitchen(Order $order): RedirectResponse
    {
        $this->sendToKitchen->execute($order->id, auth()->user()->tenant_id);

        return redirect()->route('restaurant.orders.show', $order);
    }

    public function checkout(Order $order): RedirectResponse
    {
        $this->checkoutOrder->execute($order->id, auth()->user()->tenant_id);

        return redirect()->route('restaurant.orders.index');
    }
}
