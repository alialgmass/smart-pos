<?php

namespace Modules\Customers\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Customers\Actions\CreateCustomerAction;
use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;
use Modules\Customers\Repositories\CustomerRepository;
use Modules\Customers\Repositories\DebtRepository;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly DebtRepository $debts,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Customers/Index', [
            'customers' => $this->customers->paginateForTenant(
                (int) auth()->user()->tenant_id,
                request()->only(['search', 'per_page']),
            ),
        ]);
    }

    public function store(CustomerRequest $request, CreateCustomerAction $action): RedirectResponse
    {
        $action->execute([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return redirect()->route('customers.index')->with('status', __('Customer created.'));
    }

    public function show(Customer $customer): Response
    {
        $customer->load(['debts.payments', 'loyaltyTransactions']);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('status', __('Customer updated.'));
    }

    public function debts(): Response
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $pendingDebts = $this->debts->getPendingDebts($tenantId);

        $customers = $pendingDebts->map(fn ($debt) => [
            'id' => $debt->customer->id,
            'name' => $debt->customer->name,
            'phone' => $debt->customer->phone ?? '',
            'debt_balance' => (string) $debt->debt_balance,
            'days_overdue' => (int) now()->diffInDays($debt->created_at),
        ])->values()->toArray();

        $summary = [
            'total_outstanding' => (string) $pendingDebts->sum('debt_balance'),
            'critical_overdue_count' => $pendingDebts->filter(
                fn ($debt) => now()->diffInDays($debt->created_at) > 30,
            )->count(),
            'pending_reminders' => 0,
            'collected_this_week' => '0.00',
        ];

        return Inertia::render('Customers/DebtManagement', [
            'customers' => $customers,
            'summary' => $summary,
        ]);
    }
}
