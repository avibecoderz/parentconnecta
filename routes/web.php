<?php

use App\Http\Controllers\PwaController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('manifest.webmanifest', [PwaController::class, 'manifest'])
    ->name('pwa.manifest');

Route::get('sw.js', [PwaController::class, 'serviceWorker'])
    ->name('pwa.sw');

Route::view('offline', 'pwa.offline')
    ->name('offline');

Route::post('webhooks/paystack', PaystackWebhookController::class)
    ->name('webhooks.paystack');

require __DIR__.'/platform.php';
require __DIR__.'/super_admin.php';
require __DIR__.'/school.php';
require __DIR__.'/auth.php';
