<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cases', CaseController::class)->except(['edit', 'update', 'destroy']);
    Route::post('cases/{case}/screen', [CaseController::class, 'screen'])->name('cases.screen');
    Route::post('cases/{case}/review', [CaseController::class, 'review'])
        ->middleware('role:analyst,manager')
        ->name('cases.review');
    Route::get('cases/{case}/report', [CaseController::class, 'report'])->name('cases.report');

    Route::get('risk', [RiskController::class, 'index'])->name('risk.index');
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
