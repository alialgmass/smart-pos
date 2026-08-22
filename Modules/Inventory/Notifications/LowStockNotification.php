<?php

namespace Modules\Inventory\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Inventory\Models\Product;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->product->stock_qty,
            'min_stock' => $this->product->min_stock,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Low Stock Alert: {$this->product->name}")
            ->line("Product **{$this->product->name}** is low on stock.")
            ->line("Current stock: {$this->product->stock_qty}")
            ->line("Minimum stock: {$this->product->min_stock}")
            ->action('View Product', route('inventory.products.edit', $this->product->id));
    }
}
