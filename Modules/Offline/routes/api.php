<?php

use Illuminate\Support\Facades\Route;
use Modules\Offline\Http\Controllers\OfflineSyncController;
use Modules\Offline\Http\Controllers\SyncDataController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/offline/sync', OfflineSyncController::class)->name('offline.sync');
    Route::get('/offline/sync-data', SyncDataController::class)->name('offline.sync-data');
});
