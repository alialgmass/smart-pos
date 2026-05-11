<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\InvoiceSettingsController;
use Modules\Settings\Http\Controllers\PrinterSettingsController;
use Modules\Settings\Http\Controllers\TaxSettingsController;

Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function (): void {
    Route::get('invoice', [InvoiceSettingsController::class, 'edit'])->name('invoice.edit');
    Route::put('invoice', [InvoiceSettingsController::class, 'update'])->name('invoice.update');

    Route::get('tax', [TaxSettingsController::class, 'edit'])->name('tax.edit');
    Route::put('tax', [TaxSettingsController::class, 'update'])->name('tax.update');

    Route::get('printer', [PrinterSettingsController::class, 'edit'])->name('printer.edit');
    Route::put('printer', [PrinterSettingsController::class, 'update'])->name('printer.update');
});
