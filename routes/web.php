<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->getDashboardRoute());
    }
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/superadmin.php';
require __DIR__ . '/district.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/admin_enrollment.php';
require __DIR__ . '/admin_examination.php';
require __DIR__ . '/admin_general.php';
require __DIR__ . '/school.php';
require __DIR__ . '/public.php';

