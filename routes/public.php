<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\CertificateVerificationController;

// Public routes — no authentication required
Route::get('/verify/{token}', [CertificateVerificationController::class, 'show'])
    ->name('certificate.verify');
