<?php

namespace Modules\Sales\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Sales\Actions\ProcessSaleAction;
use Modules\Sales\Models\Sale;
use Modules\Sales\Repositories\SaleRepository;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleRepository $sales,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Sale::class);

        return Inertia::render('Sales/Index', [
            'sales' => $this->sales->paginateForTenant(
                (int) auth()->user()->tenant_id,
                request()->only(['status', 'payment_method', 'date_from', 'date_to', 'search']),
            ),
        ]);
    }

    public function store(ProcessSaleAction $action): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Sale::class);

        /** @var User $user */
        $user = auth()->user();

        $sale = $action->execute(request()->all(), $user);

        if (request()->wantsJson()) {
            return response()->json(['sale' => $sale], 201);
        }

        return redirect()->route('sales.index')->with('status', __('Sale completed.'));
    }
}
