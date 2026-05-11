<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\DiscountController;
use Modules\Sales\Http\Controllers\PosController;
use Modules\Sales\Http\Controllers\ReceiptController;
use Modules\Sales\Http\Controllers\SaleController;

Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
});

Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('pos/search', [PosController::class, 'search'])->name('pos.search');
});

Route::middleware(['auth', 'verified'])->name('sales.')->group(function () {
    Route::get('sales', [SaleController::class, 'index'])->name('index');
    Route::post('sales', [SaleController::class, 'store'])->name('store');
    Route::get('receipts/{sale}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::post('discounts/preview', [DiscountController::class, 'preview'])->name('discounts.preview');
});
