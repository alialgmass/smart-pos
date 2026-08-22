<?php

namespace Modules\Restaurant\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Restaurant\Database\Factories\OrderFactory;
use Modules\Restaurant\Enums\OrderStatus;
use Modules\Shared\Concerns\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'restaurant_orders';

    protected $fillable = [
        'tenant_id',
        'table_id',
        'user_id',
        'order_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
