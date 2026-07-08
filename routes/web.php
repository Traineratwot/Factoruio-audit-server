<?php

use App\Events\AuditStarted;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ModController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

// Debug route — remove after testing
Route::get('/dev/broadcast-test', function () {
    $token = request('token', Str::uuid());

    // Method A: broadcast() helper
    broadcast(new AuditStarted(
        auditToken: $token,
        modId: 0,
        modName: 'test-mod',
        version: '1.0.0',
    ));

    return response()->json([
        'token' => $token,
        'channel' => "audit.{$token}",
        'driver' => config('broadcasting.default'),
        'instructions' => "Open console and run: echo.channel('audit.{$token}').listen('.AuditStarted', (e) => console.log('GOT:', e))",
    ]);
});
