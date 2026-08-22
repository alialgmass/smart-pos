<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Inventory\Database\Factories\ProductFactory;
use Modules\Inventory\Enums\ProductStatus;
use Modules\Shared\Concerns\BelongsToTenant;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use BelongsToTenant, HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'barcode',
        'price',
        'cost',
        'stock_qty',
        'min_stock',
        'status',
        'has_variants',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'has_variants' => 'bool',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'stock_qty' => 'decimal:2',
            'min_stock' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
