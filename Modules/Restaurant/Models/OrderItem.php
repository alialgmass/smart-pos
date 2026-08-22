<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductVariant;
use Modules\Restaurant\Database\Factories\OrderItemFactory;
use Modules\Shared\Concerns\BelongsToTenant;

class OrderItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'restaurant_order_items';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_id',
        'variant_id',
        'name',
        'price',
        'qty',
        'notes',
        'sent_to_kitchen_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'qty' => 'decimal:2',
            'sent_to_kitchen_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
