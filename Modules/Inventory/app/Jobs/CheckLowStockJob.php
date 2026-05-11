<?php

namespace Modules\Inventory\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Notifications\LowStockNotification;

class CheckLowStockJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Product::query()
            ->withoutGlobalScope('tenant')
            ->whereColumn('stock_qty', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->each(function (Product $product): void {
                $users = User::query()
                    ->where('tenant_id', $product->tenant_id)
                    ->get();

                if ($users->isNotEmpty()) {
                    Notification::send($users, new LowStockNotification($product));
                }
            });
    }
}
