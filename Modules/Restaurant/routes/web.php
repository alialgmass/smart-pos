<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\KitchenController;
use Modules\Restaurant\Http\Controllers\OrderController;
use Modules\Restaurant\Http\Controllers\TableController;

Route::middleware(['auth', 'verified'])
    ->prefix('restaurant')
    ->name('restaurant.')
    ->group(function (): void {
        Route::resource('tables', TableController::class)->except('show', 'edit');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen'])->name('orders.send-to-kitchen');
        Route::post('orders/{order}/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');

        Route::get('kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::post('kitchen/{order}/send-to-kitchen', [KitchenController::class, 'sendToKitchen'])->name('kitchen.send-to-kitchen');
        Route::post('kitchen/{order}/mark-ready', [KitchenController::class, 'markReady'])->name('kitchen.mark-ready');
        Route::get('kitchen/{order}/ticket', [KitchenController::class, 'ticket'])->name('kitchen.ticket');
    });
