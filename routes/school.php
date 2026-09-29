<?php

use Illuminate\Support\Facades\Route;
use App\Modules\School\Controllers\DashboardController;
use App\Modules\School\Controllers\StudentController;
use App\Modules\School\Controllers\EnrollmentChallanController;
use App\Modules\School\Controllers\ExamFormController;
use App\Modules\School\Controllers\ExamChallanController;
use App\Modules\School\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| School Admin Routes
|--------------------------------------------------------------------------
| All routes here require:
|   - Authentication (auth)
|   - School Admin role (role.check:school_admin)
|   - School scope injection (school.scope) — CRITICAL SECURITY
|   - Active year injection (active.year)
|
| The school_scope_id from middleware is the ONLY trusted school identifier.
| NEVER read school_id from form data or URL parameters.
*/

Route::middleware(['auth', 'force.password', 'role.check:school_admin', 'school.scope', 'active.year'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {

        // ─── Dashboard ──────────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ─── Students ───────────────────────────────────────────────────────
        // IMPORTANT: Define create/store routes BEFORE resource to prevent route conflicts
        Route::middleware('enrollment.window')->group(function () {
            Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
            Route::get('students/lookup', [StudentController::class, 'lookup'])->name('students.lookup');
            Route::post('students', [StudentController::class, 'store'])->name('students.store');
        });

        Route::resource('students', StudentController::class)
            ->only(['index', 'show', 'destroy']);
        Route::middleware('enrollment.window')->group(function () {
            Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::patch('students/{student}', [StudentController::class, 'update']);
        });

        // ─── Fee Invoices & Challans ───────────────────────────────────────
        Route::get('/invoices', [EnrollmentChallanController::class, 'index'])->name('invoices');

        Route::prefix('challans')->name('challan.')->group(function () {
            Route::get('/eligible-classes', [EnrollmentChallanController::class, 'getEligibleClasses'])->name('eligible-classes');
            Route::get('/eligible-groups', [EnrollmentChallanController::class, 'getEligibleGroups'])->name('eligible-groups');
            Route::get('/eligible-types', [EnrollmentChallanController::class, 'getEligibleStudentTypes'])->name('eligible-types');
            Route::get('/fee-rate', [EnrollmentChallanController::class, 'getFeeRate'])->name('fee-rate');
            Route::get('/eligible-students', [EnrollmentChallanController::class, 'getEligibleStudents'])->name('eligible-students');
            Route::post('/generate', [EnrollmentChallanController::class, 'generate'])->name('generate');
            Route::get('/{challan}/detail', [EnrollmentChallanController::class, 'getInvoiceDetail'])->name('detail');
            Route::get('/{challan}/download-pdf', [EnrollmentChallanController::class, 'downloadChallan'])->name('download-pdf');
            Route::get('/{challan}/download-list', [EnrollmentChallanController::class, 'downloadStudentList'])->name('download-list');
        });

        // ─── Enrollment Challans (backward-compatible legacy routes) ──────────
        Route::prefix('enrollment')->name('enrollment.')->group(function () {
            Route::get('/challans', [EnrollmentChallanController::class, 'index'])->name('challans');
            Route::middleware('enrollment.window')->group(function () {
                Route::get('/challans/create', [EnrollmentChallanController::class, 'create'])->name('challan.create');
                Route::post('/challans', [EnrollmentChallanController::class, 'store'])->name('challan.store');
                Route::post('/challans/{challan}/submit', [EnrollmentChallanController::class, 'submit'])->name('challan.submit');
            });
            Route::get('/challans/{challan}', [EnrollmentChallanController::class, 'show'])->name('challan.show');
        });

        // ─── Examination Forms ────────────────────────────────────────────
        Route::prefix('examination')->name('examination.')->group(function () {
            Route::get('/forms', [ExamFormController::class, 'index'])->name('forms');
            Route::get('/gap-report', [ExamFormController::class, 'gapReport'])->name('gap-report');
            Route::get('/gap-report/pdf', [DocumentController::class, 'examGapReportPdf'])->name('gap-report.pdf');
            Route::patch('/forms/{examForm}/save-final', [ExamFormController::class, 'saveFinal'])->name('form.save-final');
            Route::middleware('examination.window')->group(function () {
                Route::get('/forms/create', [ExamFormController::class, 'create'])->name('form.create');
                Route::post('/forms', [ExamFormController::class, 'store'])->name('form.store');
                Route::get('/forms/{examForm}/edit', [ExamFormController::class, 'edit'])->name('form.edit');
                Route::put('/forms/{examForm}', [ExamFormController::class, 'update'])->name('form.update');
                Route::delete('/forms/{examForm}', [ExamFormController::class, 'destroy'])->name('form.destroy');
                Route::post('/forms/{examForm}/submit', [ExamFormController::class, 'submit'])->name('form.submit');

                Route::get('/challans/create', [ExamChallanController::class, 'create'])->name('challan.create');
                Route::post('/challans', [ExamChallanController::class, 'store'])->name('challan.store');
            });
            Route::get('/forms/{examForm}', [ExamFormController::class, 'show'])->name('form.show');

            Route::get('/challans', [ExamChallanController::class, 'index'])->name('challans');
        });

        // ─── Printable Documents (opens in new tab, browser print) ───────
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/challan/{challan}/print', [DocumentController::class, 'enrollmentChallan'])->name('challan.print');
            Route::get('/challan/{challan}/students', [DocumentController::class, 'challanStudentList'])->name('challan.students');
            Route::get('/exam-challan/{challan}/print', [DocumentController::class, 'examChallan'])->name('exam-challan.print');
            Route::get('/admission-slip/{student}', [DocumentController::class, 'admissionSlip'])->name('admission-slip');

            // Enrollment & Exam form PDF downloads
            Route::get('/enrollment-form/{student}', [DocumentController::class, 'enrollmentFormPdf'])->name('enrollment-form.pdf');
            Route::get('/enrollment-forms/bulk', [DocumentController::class, 'enrollmentFormBulkPdf'])->name('enrollment-forms.bulk-pdf');
            Route::get('/exam-form/{examForm}', [DocumentController::class, 'examFormPdf'])->name('exam-form.pdf');
            Route::get('/exam-forms/bulk', [DocumentController::class, 'examFormBulkPdf'])->name('exam-forms.bulk-pdf');
        });
    });
