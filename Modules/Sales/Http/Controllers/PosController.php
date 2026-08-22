<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\Category;
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
        $query = (string) request()->get('q', '');
        $categoryId = request()->filled('category_id') ? (int) request()->get('category_id') : null;
        $limit = min(max((int) request()->input('limit', 100), 1), 500);
        $tenantId = (int) auth()->user()->tenant_id;

        $products = $repository->search($tenantId, $query, $limit, $categoryId);

        return response()->json($products);
    }

    public function categories(): JsonResponse
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $categories = Category::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }
}
