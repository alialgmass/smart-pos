<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Actions\CreateProductAction;
use Modules\Inventory\Actions\DeleteProductAction;
use Modules\Inventory\Actions\UpdateProductAction;
use Modules\Inventory\Http\Requests\ProductRequest;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Repositories\ProductRepository;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Inventory/Products/Index', [
            'products' => $this->products->paginateForTenant(
                (int) auth()->user()->tenant_id,
                request()->only(['search', 'category_id', 'status', 'per_page']),
            ),
            'categories' => Category::query()
                ->withoutGlobalScope('tenant')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
        ]);
    }

    public function store(ProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        $action->execute([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return redirect()->route('inventory.products.index')->with('status', __('Product created.'));
    }

    public function update(ProductRequest $request, Product $product, UpdateProductAction $action): RedirectResponse
    {
        $action->execute($product, $request->validated());

        return redirect()->route('inventory.products.index')->with('status', __('Product updated.'));
    }

    public function destroy(Product $product, DeleteProductAction $action): RedirectResponse
    {
        $action->execute($product);

        return redirect()->route('inventory.products.index')->with('status', __('Product deleted.'));
    }
}
