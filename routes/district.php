<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DistrictAdmin\DashboardController;

Route::middleware(['auth', 'force.password', 'role.check:district_admin', 'active.year'])
    ->prefix('district')
    ->name('district.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', fn () => inertia('district/Schools'))->name('schools');
        Route::get('/schools/{school}', fn () => inertia('district/SchoolShow'))->name('schools.show');
        Route::get('/reports', fn () => inertia('district/Reports'))->name('reports');
        Route::get('/announcements', fn () => inertia('district/Announcements'))->name('announcements');
    });
