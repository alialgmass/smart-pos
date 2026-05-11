<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\WebhookController;

Route::post('/billing/webhook', [WebhookController::class, '__invoke'])->name('billing.webhook');
