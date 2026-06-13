<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;
use App\Models\Report;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $reports = Report::all(); // или ->get(), или paginate()

    return Inertia::render('welcome', [
        'reports' => $reports,
    ]);
})->name('home');

Route::prefix('report/')
    ->name('report.')
    ->group(function () {
//        Route::get('mod/{name}', [ReportController::class, 'index'])->name('mod');
        Route::get('mod/{mod}/version/{version}', [ReportController::class, 'index'])->name('mod.version');
    });
