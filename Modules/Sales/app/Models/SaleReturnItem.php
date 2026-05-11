<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sales\Database\Factories\SaleReturnItemFactory;
use Modules\Shared\Concerns\BelongsToTenant;

class SaleReturnItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'variant_id',
        'qty',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    protected static function newFactory(): Factory
    {
        return SaleReturnItemFactory::new();
    }
}
