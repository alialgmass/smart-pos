<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Sales\Models\Sale;
use Modules\Sales\Repositories\PosProductSearchRepository;

class PosController extends Controller
{
    public function __construct(
        private readonly PosProductSearchRepository $productSearch,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Sale::class);

        return Inertia::render('Sales/Pos/Index');
    }

    public function search(PosProductSearchRepository $repository): JsonResponse
    {
        $query = request()->get('q', '');
        $tenantId = (int) auth()->user()->tenant_id;

        $products = $repository->search($tenantId, $query);

        return response()->json($products);
    }
}
