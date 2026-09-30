<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DistrictAdmin\DashboardController;

Route::middleware(['auth', 'force.password', 'role.check:district_admin', 'active.year'])
    ->prefix('district')
    ->name('district.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', [DashboardController::class, 'schools'])->name('schools');
        Route::get('/schools/{school}', [DashboardController::class, 'schoolShow'])->name('schools.show');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/announcements', [DashboardController::class, 'announcements'])->name('announcements');
    });
