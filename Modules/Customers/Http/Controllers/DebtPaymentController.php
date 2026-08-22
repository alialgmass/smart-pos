<?php

namespace Modules\Customers\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Customers\Actions\RecordDebtPaymentAction;

class DebtPaymentController extends Controller
{
    public function store(Request $request, RecordDebtPaymentAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'debt_id' => ['required', 'exists:customer_debts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'integer', 'in:1,2,3,4'],
        ]);

        try {
            $action->execute(
                (int) $validated['debt_id'],
                (float) $validated['amount'],
                (int) $validated['payment_method'],
            );
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('status', __('Payment recorded.'));
    }
}
