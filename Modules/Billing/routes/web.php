<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PricingController;
use Modules\Billing\Http\Controllers\SubscriptionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/billing', [SubscriptionController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('billing.checkout');
});

Route::get('/pricing', [PricingController::class, 'index'])->name('billing.pricing');
