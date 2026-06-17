<?php

use App\Http\Controllers\ModController;
use App\Http\Controllers\ModSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ModController::class, 'index'])->name('home');

Route::get('/search/mods', [ModSearchController::class, 'search'])->name('mods.search');

Route::prefix('report/')
    ->name('report.')
    ->group(function () {
        Route::get('mod/{mod}', [ModController::class, 'report'])->name('mod');
        Route::get('mod/{mod}/version/{version}', [ModController::class, 'report'])->name('mod.version');
        Route::get('search/{search}', [ModController::class, 'search'])->name('mod.version');
    });
