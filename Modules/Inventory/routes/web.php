<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\BarcodeController;
use Modules\Inventory\Http\Controllers\CategoryController;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\ProductImportController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;

Route::middleware(['auth', 'verified'])
    ->prefix('inventory')
    ->name('inventory.')
    ->group(function (): void {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except('show');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::post('products/import/preview', [ProductImportController::class, 'preview'])->name('products.import.preview');
        Route::post('products/import/confirm', [ProductImportController::class, 'confirm'])->name('products.import.confirm');
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('products/{product}/barcode', [BarcodeController::class, 'show'])->name('products.barcode');
    });
