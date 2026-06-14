<?php

use App\Http\Controllers\ModController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ModController::class, 'index'])->name('home');

Route::prefix('report/')
    ->name('report.')
    ->group(function () {
        Route::get('mod/{mod}', [ModController::class, 'report'])->name('mod');
        Route::get('mod/{mod}/version/{version}', [ModController::class, 'report'])->name('mod.version');
    });
