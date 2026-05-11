<?php

namespace Modules\Customers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Customers\Database\Factories\DebtPaymentFactory;
use Modules\Shared\Concerns\BelongsToTenant;
use Modules\Shared\Enums\PaymentMethod;

class DebtPayment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'debt_id',
        'amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(CustomerDebt::class, 'debt_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): DebtPaymentFactory
    {
        return DebtPaymentFactory::new();
    }
}
