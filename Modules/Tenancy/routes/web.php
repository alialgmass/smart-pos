<?php

use Illuminate\Support\Facades\Route;
use Modules\Tenancy\Http\Controllers\RegisteredTenantController;

Route::middleware('guest')->group(function (): void {
    Route::get('tenant/register', [RegisteredTenantController::class, 'create'])->name('tenant.register');
    Route::post('tenant/register', [RegisteredTenantController::class, 'store'])->name('tenant.register.store');
});

Route::get('tenant/register/check-inbox', [RegisteredTenantController::class, 'checkInbox'])
    ->middleware('auth')
    ->name('tenant.register.check-inbox');
