<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Actions\ReorderCategoriesAction;
use Modules\Inventory\Http\Requests\CategoryRequest;
use Modules\Inventory\Models\Category;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Inventory/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return redirect()->route('inventory.categories.index')->with('status', __('Category created.'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('inventory.categories.index')->with('status', __('Category updated.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('inventory.categories.index')->with('status', __('Cannot delete category with products.'));
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')->with('status', __('Category deleted.'));
    }

    public function reorder(ReorderCategoriesAction $action): RedirectResponse
    {
        $action->execute(request()->input('ordered_ids', []));

        return redirect()->route('inventory.categories.index')->with('status', __('Categories reordered.'));
    }
}
