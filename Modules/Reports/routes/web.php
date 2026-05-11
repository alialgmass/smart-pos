<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\CashierReportController;
use Modules\Reports\Http\Controllers\DashboardController;
use Modules\Reports\Http\Controllers\DebtReportController;
use Modules\Reports\Http\Controllers\ProfitReportController;
use Modules\Reports\Http\Controllers\ReportExportController;
use Modules\Reports\Http\Controllers\TopProductsReportController;

Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('top-products', [TopProductsReportController::class, 'index'])->name('top-products');
    Route::get('profit', [ProfitReportController::class, 'index'])->name('profit');
    Route::get('cashiers', [CashierReportController::class, 'index'])->name('cashiers');
    Route::get('debts', [DebtReportController::class, 'index'])->name('debts');
    Route::post('export', [ReportExportController::class, 'download'])->name('export');
});
