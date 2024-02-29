<?php

use BuckhamDuffy\LaravelTwoFactor\Http\Controllers\TwoFactorApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/two-factor')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/', [TwoFactorApiController::class, 'show']);
        Route::put('/', [TwoFactorApiController::class, 'update']);
        Route::post('/confirm', [TwoFactorApiController::class, 'confirm']);
        Route::get('/show-secret', [TwoFactorApiController::class, 'showSecret']);
        Route::get('/show-qrcode', [TwoFactorApiController::class, 'showQrCode']);
        Route::get('/show-recovery-codes', [TwoFactorApiController::class, 'showRecoveryCodes']);
    });
