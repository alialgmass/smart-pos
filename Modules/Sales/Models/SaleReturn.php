<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Sales\Database\Factories\SaleReturnFactory;
use Modules\Sales\Enums\RefundMethod;
use Modules\Shared\Concerns\BelongsToTenant;

class SaleReturn extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'customer_id',
        'user_id',
        'refund_method',
        'total_refund',
    ];

    protected function casts(): array
    {
        return [
            'refund_method' => RefundMethod::class,
            'total_refund' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    protected static function newFactory(): Factory
    {
        return SaleReturnFactory::new();
    }
}
