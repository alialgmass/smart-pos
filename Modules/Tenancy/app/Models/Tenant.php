<?php

namespace Modules\Tenancy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Billing\Models\Subscription;
use Modules\Tenancy\Database\Factories\TenantFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'settings',
        'plan_id',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    protected static function newFactory(): Factory
    {
        return TenantFactory::new();
    }
}
