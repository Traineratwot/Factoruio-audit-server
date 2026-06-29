<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\ModController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ModController::class, 'index'])->name('home');

Route::get('/search/mods', [ModController::class, 'search'])->name('mods.search');

Route::prefix('report/')
    ->name('report.')
    ->group(function () {
        Route::get('mod/{mod}', [ModController::class, 'report'])->name('mod');
        Route::get('mod/{mod}/version/{version}', [ModController::class, 'report'])->name('mod.version');
        Route::get('search/{search}', [ModController::class, 'search'])->name('mod.version');
    });

Route::get('/api/mods/search', [AuditController::class, 'search'])->name('api.mod.search');
Route::get('/api/mods/{mod}/versions', [AuditController::class, 'versions'])->name('api.mod.versions');
Route::post('/audit', [AuditController::class, 'store'])->name('audit.store');
