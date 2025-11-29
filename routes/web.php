<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmissionFactorController;
use App\Http\Controllers\Scope1EmissionController;
use App\Http\Controllers\Scope2EmissionController;
use App\Http\Controllers\Scope3EmissionController;
use App\Http\Controllers\StakeholderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Konfigurasi (Emission Factors)
    Route::resource('konfigurasi', EmissionFactorController::class)->except(['show']);

    // Scope 1 Emissions
    Route::resource('scope-1', Scope1EmissionController::class)->except(['show']);

    // Scope 2 Emissions
    Route::resource('scope-2', Scope2EmissionController::class)->except(['show']);

    // Scope 3 Emissions
    Route::resource('scope-3', Scope3EmissionController::class)->except(['show']);

    // Stakeholders
    Route::resource('stakeholders', StakeholderController::class)->except(['show']);
});

require __DIR__.'/settings.php';
