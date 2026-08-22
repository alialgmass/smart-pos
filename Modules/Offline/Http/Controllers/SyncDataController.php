<?php

namespace Modules\Offline\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Product;

class SyncDataController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $lastSync = $request->get('last_synced_at');

        $productsQuery = Product::query()->where('tenant_id', $tenantId);
        $categoriesQuery = Category::query()->where('tenant_id', $tenantId);
        $customersQuery = Customer::query()->where('tenant_id', $tenantId);

        if ($lastSync !== null) {
            $productsQuery->where('updated_at', '>', $lastSync);
            $categoriesQuery->where('updated_at', '>', $lastSync);
            $customersQuery->where('updated_at', '>', $lastSync);
        }

        return response()->json([
            'products' => $productsQuery->get(['id', 'name', 'sku', 'price', 'cost', 'stock_qty', 'category_id', 'updated_at']),
            'categories' => $categoriesQuery->get(['id', 'name', 'sort_order', 'updated_at']),
            'customers' => $customersQuery->get(['id', 'name', 'email', 'phone', 'updated_at']),
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
