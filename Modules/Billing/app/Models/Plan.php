<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Billing\Database\Factories\PlanFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_monthly',
        'max_users',
        'max_products',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'max_users' => 'integer',
            'max_products' => 'integer',
            'features' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected static function newFactory(): Factory
    {
        return PlanFactory::new();
    }
}
