<?php

use Illuminate\Support\Facades\Route;
use Modules\Offline\Http\Controllers\OfflineController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('offlines', OfflineController::class)->names('offline');
});
