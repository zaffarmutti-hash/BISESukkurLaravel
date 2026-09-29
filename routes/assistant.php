<?php

use App\Http\Controllers\AssistantDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'force.password',
    'role.check:assistant_controller',
    'permission:invoice.view',
    'active.year',
    'throttle:60,1',
])->prefix('assistant')->name('assistant.')->group(function () {
    Route::get('/dashboard', [AssistantDashboardController::class, 'index'])->name('dashboard');
});