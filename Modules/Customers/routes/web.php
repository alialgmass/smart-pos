<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomerController;
use Modules\Customers\Http\Controllers\CustomerSearchController;
use Modules\Customers\Http\Controllers\DebtPaymentController;

Route::middleware(['auth', 'verified'])->name('customers.')->group(function (): void {
    Route::get('customers', [CustomerController::class, 'index'])->name('index');
    Route::post('customers', [CustomerController::class, 'store'])->name('store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::get('customers/search/list', [CustomerSearchController::class, 'search'])->name('search');
    Route::post('debt-payments', [DebtPaymentController::class, 'store'])->name('debt-payments.store');
});
