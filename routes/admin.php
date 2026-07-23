<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\DashboardController;
use App\Modules\Admin\Controllers\AcademicYearController;
use App\Modules\Admin\Controllers\SchoolController;
use App\Modules\Admin\Controllers\FeeStructureController;
use App\Modules\Admin\Controllers\ChallanApprovalController;
use App\Modules\Admin\Controllers\EnrollmentNumberController;
use App\Modules\Admin\Controllers\ExamCenterController;
use App\Modules\Admin\Controllers\ExamTimetableController;
use App\Modules\Admin\Controllers\ExamFormApprovalController;
use App\Modules\Admin\Controllers\SeatAssignmentController;
use App\Modules\Admin\Controllers\ResultEntryController;
use App\Modules\Admin\Controllers\CertificateController;
use App\Modules\Admin\Controllers\YearRolloverController;
use App\Modules\Admin\Controllers\ExceptionController;
use App\Modules\Admin\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
| All routes here require:
|   - Authentication (auth)
|   - Super Admin role (role.check:super_admin)
|   - Active year injection (active.year)
|
| The active year is ALWAYS resolved server-side via middleware.
*/

Route::middleware(['auth', 'role.check:super_admin', 'active.year'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ─── Dashboard ──────────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ─── Academic Year Management ────────────────────────────────────────
        Route::prefix('years')->name('years.')->group(function () {
            Route::get('/', [AcademicYearController::class, 'index'])->name('index');
            Route::post('/', [AcademicYearController::class, 'store'])->name('store');
            Route::post('/{year}/activate', [AcademicYearController::class, 'setActive'])->name('activate');
            Route::post('/{year}/enrollment/open', [AcademicYearController::class, 'openEnrollmentWindow'])->name('enrollment.open');
            Route::post('/{year}/enrollment/close', [AcademicYearController::class, 'closeEnrollmentWindow'])->name('enrollment.close');
            Route::post('/{year}/examination/open', [AcademicYearController::class, 'openExaminationWindow'])->name('examination.open');
            Route::post('/{year}/examination/close', [AcademicYearController::class, 'closeExaminationWindow'])->name('examination.close');
        });

        // ─── School Management ───────────────────────────────────────────────
        Route::resource('schools', SchoolController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        // ─── Fee Structures ──────────────────────────────────────────────────
        Route::resource('fees', FeeStructureController::class)->only(['index', 'store', 'update']);

        // ─── Enrollment Challan Approval ─────────────────────────────────────
        Route::prefix('enrollment')->name('enrollment.')->group(function () {
            Route::get('/challans', [ChallanApprovalController::class, 'pendingEnrollment'])->name('challans');
            Route::get('/challans/{challan}', [ChallanApprovalController::class, 'show'])->name('challan.show');
            Route::post('/challans/{challan}/confirm', [ChallanApprovalController::class, 'confirm'])->name('challan.confirm');
            Route::post('/challans/{challan}/reject', [ChallanApprovalController::class, 'reject'])->name('challan.reject');
            Route::get('/reports', [ReportController::class, 'enrollment'])->name('reports');
        });

        // ─── Examination Management ──────────────────────────────────────────
        Route::prefix('examination')->name('examination.')->group(function () {
            Route::get('/challans', [ChallanApprovalController::class, 'pendingExamination'])->name('challans');
            Route::post('/challans/{challan}/confirm', [ChallanApprovalController::class, 'confirm'])->name('challan.confirm');
            Route::post('/challans/{challan}/reject', [ChallanApprovalController::class, 'reject'])->name('challan.reject');
            Route::resource('centers', ExamCenterController::class)->only(['index', 'store', 'update']);
            Route::resource('timetable', ExamTimetableController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('/seats/assign', [SeatAssignmentController::class, 'assignAll'])->name('seats.assign');
            Route::get('/results', [ResultEntryController::class, 'index'])->name('results.index');
            Route::post('/results/bulk', [ResultEntryController::class, 'bulkStore'])->name('results.bulk');
            Route::post('/certificates/generate', [CertificateController::class, 'generateBatch'])->name('certificates.generate');
        });

        // ─── Year Rollover (dangerous — requires confirmation) ───────────────
        Route::post('/rollover', [YearRolloverController::class, 'execute'])->name('rollover');

        // ─── School Exceptions ───────────────────────────────────────────────
        Route::resource('exceptions', ExceptionController::class)->only(['index', 'create', 'store', 'update']);

        // ─── Reports ─────────────────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/districts', [ReportController::class, 'district'])->name('district');
            Route::get('/schools', [ReportController::class, 'school'])->name('school');
            Route::get('/audit', [ReportController::class, 'audit'])->name('audit');
        });

        // ─── Certificate Public Verification ─────────────────────────────────
        // Note: This route is NOT behind auth so anyone can verify a certificate
    });

// Public certificate verification — no auth required
Route::get('/certificates/{token}/verify', [CertificateController::class, 'verify'])
    ->name('certificates.verify');
