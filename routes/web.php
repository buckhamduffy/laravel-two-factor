<?php

use BuckhamDuffy\LaravelTwoFactor\Http\Controllers\TwoFactorWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/two-factor-confirmation/{type?}', [TwoFactorWebController::class, 'show'])->name('two-factor-confirm');
        Route::post('/two-factor-confirmation', [TwoFactorWebController::class, 'store'])->name('two-factor-confirm.store');
        Route::post('/two-factor-confirmation/resend', [TwoFactorWebController::class, 'resend'])->name('two-factor-confirm.resend');
        Route::get('/two-factor-recovery', [TwoFactorWebController::class, 'showRecovery'])->name('two-factor-recovery');
        Route::post('/two-factor-recovery', [TwoFactorWebController::class, 'storeRecovery'])->name('two-factor-recovery.store');
    });
