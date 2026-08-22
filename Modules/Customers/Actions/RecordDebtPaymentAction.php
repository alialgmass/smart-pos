<?php

namespace Modules\Customers\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customers\Enums\CustomerDebtStatus;
use Modules\Customers\Models\CustomerDebt;
use Modules\Customers\Models\DebtPayment;

class RecordDebtPaymentAction
{
    public function execute(int $debtId, float $amount, int $paymentMethod): DebtPayment
    {
        return DB::transaction(function () use ($debtId, $amount, $paymentMethod): DebtPayment {
            $debt = CustomerDebt::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->findOrFail($debtId);

            $newPaidAmount = $debt->paid_amount + $amount;

            if ($newPaidAmount > $debt->amount) {
                throw new \RuntimeException(__('Payment exceeds remaining debt.'));
            }

            $status = match (true) {
                $newPaidAmount >= $debt->amount => CustomerDebtStatus::Paid,
                $newPaidAmount > 0 => CustomerDebtStatus::PartialPaid,
                default => CustomerDebtStatus::Open,
            };

            $debt->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);

            $customer = $debt->customer()->withoutGlobalScope('tenant')->first();
            $customer->decrement('debt_balance', $amount);

            return DebtPayment::create([
                'tenant_id' => $debt->tenant_id,
                'debt_id' => $debtId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'user_id' => Auth::id(),
            ]);
        });
    }
}
