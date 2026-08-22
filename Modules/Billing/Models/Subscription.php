<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Database\Factories\SubscriptionFactory;
use Modules\Shared\Concerns\BelongsToTenant;
use Modules\Shared\Enums\Gateway;
use Modules\Shared\Enums\SubscriptionStatus;
use Modules\Tenancy\Models\Tenant;

class Subscription extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'gateway',
        'gateway_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'gateway' => Gateway::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    protected static function newFactory(): Factory
    {
        return SubscriptionFactory::new();
    }
}
