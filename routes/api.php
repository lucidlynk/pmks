<?php

use App\Http\Controllers\Api\StatistikController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['api.token', 'throttle:60,1'])
    ->group(function () {
        // PMKS & PSKS
        Route::get('/statistik/pmks',                 [StatistikController::class, 'pmks']);
        Route::get('/statistik/psks',                 [StatistikController::class, 'psks']);
        Route::get('/statistik/ringkasan',            [StatistikController::class, 'ringkasan']);
        Route::get('/statistik/kecamatan',            [StatistikController::class, 'perKecamatan']);
        Route::get('/statistik/desa/{kecamatan_id?}', [StatistikController::class, 'perDesa']);

        // DTSEN
        Route::get('/statistik/dtsen',                [StatistikController::class, 'dtsen']);

        // KIS
        Route::get('/statistik/kis',                  [StatistikController::class, 'kis']);

        // Bansos PKH & Sembako
        Route::get('/statistik/bansos',               [StatistikController::class, 'bansos']);
    });
