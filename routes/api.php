<?php

use App\Http\Controllers\Api\StatistikController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Statistik publik (tidak perlu auth)
    Route::get('/statistik/pmks', [StatistikController::class, 'pmks']);
    Route::get('/statistik/psks', [StatistikController::class, 'psks']);
    Route::get('/statistik/ringkasan', [StatistikController::class, 'ringkasan']);
    Route::get('/statistik/kecamatan', [StatistikController::class, 'perKecamatan']);
    Route::get('/statistik/desa/{kecamatan_id?}', [StatistikController::class, 'perDesa']);
});
