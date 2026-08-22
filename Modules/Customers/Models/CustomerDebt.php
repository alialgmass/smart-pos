<?php

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Customers\Database\Factories\CustomerDebtFactory;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Shared\Concerns\BelongsToTenant;

class CustomerDebt extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'sale_id',
        'amount',
        'paid_amount',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'status' => CustomerDebtStatus::class,
            'due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id');
    }

    protected static function newFactory(): CustomerDebtFactory
    {
        return CustomerDebtFactory::new();
    }
}
