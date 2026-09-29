<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\SuperAdmin\AcademicYearTransitionController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\AnnouncementController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\EnrollmentHubController;
use App\Http\Controllers\SuperAdmin\ExamHubController;
use App\Http\Controllers\SuperAdmin\ReportsAnalyticsController;
use App\Http\Controllers\SuperAdmin\SpecialPermissionController;
use App\Http\Controllers\SuperAdmin\SystemHealthController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\SystemSettingsController;
use App\Http\Controllers\SuperAdmin\WindowOverrideController;
use App\Modules\Admin\Controllers\AcademicYearController;
use App\Modules\Admin\Controllers\SchoolController;
use App\Modules\Admin\Controllers\FeeStructureController;
use App\Modules\Admin\Controllers\ChallanApprovalController;
use App\Modules\Admin\Controllers\ReportController;
use App\Modules\Admin\Controllers\ExamCenterController;
use App\Modules\Admin\Controllers\ExamTimetableController;
use App\Modules\Admin\Controllers\SeatAssignmentController;
use App\Modules\Admin\Controllers\ResultEntryController;
use App\Modules\Admin\Controllers\CertificateController;
use App\Modules\Admin\Controllers\EnrollmentNumberController;

Route::middleware(['auth', 'force.password', 'superadmin', 'active.year', 'throttle:60,1'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        // ─── Overview ───────────────────────────────────────────────────────
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/health', [SystemHealthController::class, 'index'])->name('health');

        // ─── Enrollment Hub ─────────────────────────────────────────────────
        Route::get('/enrollment-hub', [EnrollmentHubController::class, 'index'])
            ->middleware('permission:enrollment.view')
            ->name('enrollment-hub');

        Route::get('/invoice-verification', [ChallanApprovalController::class, 'index'])
            ->middleware('permission:invoice.verify')
            ->name('invoice-verification');

        Route::get('/invoices/verify', [ChallanApprovalController::class, 'index'])
            ->middleware('permission:invoice.verify')
            ->name('invoices.verify');

        Route::get('/invoices/{challan}/detail', [ChallanApprovalController::class, 'getDetail'])
            ->middleware('permission:invoice.verify')
            ->name('invoices.detail');

        Route::get('/invoices/{challan}/download-challan', [ChallanApprovalController::class, 'downloadChallan'])
            ->middleware('permission:invoice.view')
            ->name('invoices.download-challan');

        Route::get('/invoices/{challan}/download-list', [ChallanApprovalController::class, 'downloadStudentList'])
            ->middleware('permission:invoice.view')
            ->name('invoices.download-list');

        Route::post('/invoices/bulk-verify', [ChallanApprovalController::class, 'bulkVerify'])
            ->middleware('permission:invoice.verify')
            ->name('invoices.bulk-verify');

        Route::get('/invoice-verification/{challan}', [ChallanApprovalController::class, 'show'])
            ->middleware('permission:invoice.verify')
            ->name('invoice-verification.show');

        Route::post('/invoice-verification/{challan}/confirm', [ChallanApprovalController::class, 'confirm'])
            ->middleware('permission:invoice.verify')
            ->name('invoice-verification.confirm');

        Route::post('/invoice-verification/{challan}/reject', [ChallanApprovalController::class, 'reject'])
            ->middleware('permission:invoice.verify')
            ->name('invoice-verification.reject');

        // ─── Allotment History & Manual Allotment ───────────────────────────
        Route::get('/allotment-history', [EnrollmentNumberController::class, 'index'])
            ->middleware('permission:allotment.history')
            ->name('allotment-history');

        Route::get('/enrollment/allotment', [EnrollmentNumberController::class, 'index'])
            ->middleware('permission:allotment.history')
            ->name('enrollment.allotment');

        Route::get('/enrollment/search-student-manual', [EnrollmentNumberController::class, 'searchStudentForManual'])
            ->middleware('permission:allotment.history')
            ->name('enrollment.search-student-manual');

        Route::post('/enrollment/manual-allot', [EnrollmentNumberController::class, 'manualAllot'])
            ->middleware('permission:allotment.history')
            ->name('enrollment.manual-allot');

        Route::post('/allotment/run', [EnrollmentNumberController::class, 'runAllotment'])
            ->middleware('permission:allotment.history')
            ->name('allotment.run');

        Route::get('/payment-transactions', [ChallanApprovalController::class, 'transactions'])
            ->middleware('permission:invoice.view')
            ->name('payment-transactions');

        Route::get('/invoices/transactions', [ChallanApprovalController::class, 'transactions'])
            ->middleware('permission:invoice.view')
            ->name('invoices.transactions');

        Route::get('/reports/fees', [ReportController::class, 'feeCollection'])
            ->middleware('permission:report.fee_collection')
            ->name('reports.fees');

        // Enrollment direct tab routes
        Route::get('/enrollment', [EnrollmentHubController::class, 'index'])
            ->middleware('permission:enrollment.view')
            ->name('enrollment');

        Route::get('/enrollment/window', function (Request $request) {
            $request->merge(['tab' => 'window']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:enrollment.view')->name('enrollment.window');

        Route::get('/enrollment/fees', function (Request $request) {
            $request->merge(['tab' => 'fees']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:enrollment.view')->name('enrollment.fees');

        Route::get('/enrollment/verify', function (Request $request) {
            $request->merge(['tab' => 'verify']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:invoice.verify')->name('enrollment.verify');

        Route::get('/enrollment/allotment', function (Request $request) {
            $request->merge(['tab' => 'allotment']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:enrollment.view')->name('enrollment.allotment');

        Route::get('/enrollment/permissions', function (Request $request) {
            $request->merge(['tab' => 'permissions']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:settings.system_settings')->name('enrollment.permissions');

        Route::get('/enrollment/reports', function (Request $request) {
            $request->merge(['tab' => 'reports']);
            return app(EnrollmentHubController::class)->index($request);
        })->middleware('permission:enrollment.view')->name('enrollment.reports');

        Route::middleware('permission:academicyear.manage_windows')->group(function () {
            Route::get('/fee-windows', [AcademicYearController::class, 'index'])->name('fee-windows');
            Route::get('/settings/enrollment-windows', [AcademicYearController::class, 'index'])->name('settings.enrollment-windows');
            Route::get('/settings/exam-windows', [AcademicYearController::class, 'index'])->name('settings.exam-windows');
        });

        Route::middleware('permission:feerate.view')->prefix('fee-rates')->name('fee-rates.')->group(function () {
            Route::get('/', [FeeStructureController::class, 'index'])->name('index');
            Route::post('/', [FeeStructureController::class, 'store'])
                ->middleware('permission:feerate.create')
                ->name('store');
            Route::put('/{fee}', [FeeStructureController::class, 'update'])
                ->middleware('permission:feerate.edit')
                ->name('update');
        });

        Route::get('/reports/enrollment', [ReportController::class, 'enrollment'])
            ->middleware('permission:report.enrollment')
            ->name('reports.enrollment');

        // ─── Examination Hub ────────────────────────────────────────────────
        Route::get('/examination-hub', [ExamHubController::class, 'index'])
            ->middleware('permission:examination.view')
            ->name('examination-hub');

        Route::get('/exam', [ExamHubController::class, 'index'])
            ->middleware('permission:examination.view')
            ->name('exam');

        Route::get('/exam/window', function (Request $request) {
            $request->merge(['tab' => 'window']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.window');

        Route::get('/exam/fees', function (Request $request) {
            $request->merge(['tab' => 'fees']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.fees');

        Route::get('/exam/seats', function (Request $request) {
            $request->merge(['tab' => 'seats']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.seats');

        Route::get('/exam/centers', function (Request $request) {
            $request->merge(['tab' => 'centers']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.centers');

        Route::get('/exam/timetable', function (Request $request) {
            $request->merge(['tab' => 'timetable']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.timetable');

        Route::get('/exam/results', function (Request $request) {
            $request->merge(['tab' => 'results']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.results');

        Route::get('/exam/reports', function (Request $request) {
            $request->merge(['tab' => 'reports']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.reports');

        Route::get('/exam/certificates', function (Request $request) {
            $request->merge(['tab' => 'certificates']);
            return app(ExamHubController::class)->index($request);
        })->middleware('permission:examination.view')->name('exam.certificates');

        Route::get('/exam-invoice-verification', [ChallanApprovalController::class, 'pendingExamination'])
            ->middleware('permission:invoice.verify')
            ->name('exam-invoice-verification');

        Route::get('/reports/gap', [ReportController::class, 'gap'])
            ->middleware('permission:report.missing_examforms')
            ->name('reports.gap');

        Route::middleware('permission:examination.view')->prefix('examination')->name('examination.')->group(function () {
            Route::get('/centers', function (Request $request) {
                $request->merge(['tab' => 'centers']);
                return app(ExamHubController::class)->index($request);
            })->name('centers.index');
            Route::post('/centers', [ExamCenterController::class, 'store'])->name('centers.store');
            Route::put('/centers/{center}', [ExamCenterController::class, 'update'])->name('centers.update');
            Route::delete('/centers/{center}', [ExamCenterController::class, 'destroy'])->name('centers.destroy');

            Route::get('/timetable', function (Request $request) {
                $request->merge(['tab' => 'timetable']);
                return app(ExamHubController::class)->index($request);
            })->name('timetable.index');
            Route::post('/timetable', [ExamTimetableController::class, 'store'])->name('timetable.store');
            Route::put('/timetable/{timetable}', [ExamTimetableController::class, 'update'])->name('timetable.update');
            Route::delete('/timetable/{timetable}', [ExamTimetableController::class, 'destroy'])->name('timetable.destroy');

            Route::get('/results', function (Request $request) {
                $request->merge(['tab' => 'results']);
                return app(ExamHubController::class)->index($request);
            })->name('results.index');
            Route::post('/results', [ResultEntryController::class, 'store'])->name('results.store');
            Route::post('/results/{result}/verify', [ResultEntryController::class, 'verify'])->name('results.verify');

            Route::get('/seat-allotment', function (Request $request) {
                $request->merge(['tab' => 'seats']);
                return app(ExamHubController::class)->index($request);
            })->name('seat-allotment.index');
            Route::post('/seat-allotment/run', [SeatAssignmentController::class, 'runAllotment'])->name('seat-allotment.run');

            Route::get('/certificates', function (Request $request) {
                $request->merge(['tab' => 'certificates']);
                return app(ExamHubController::class)->index($request);
            })->name('certificates.index');
            Route::post('/certificates/generate', [CertificateController::class, 'generate'])->name('certificates.generate');
        });

        // ─── Schools and Users ──────────────────────────────────────────────
        Route::middleware('permission:school.view')->group(function () {
            Route::get('/schools', [SchoolController::class, 'index'])->name('schools');
            Route::get('/schools/create', [SchoolController::class, 'create'])
                ->middleware('permission:school.create')
                ->name('schools.create');
            Route::post('/schools', [SchoolController::class, 'store'])
                ->middleware('permission:school.create')
                ->name('schools.store');
            Route::get('/schools/{school}', [SchoolController::class, 'show'])
                ->name('schools.show');
            Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])
                ->middleware('permission:school.edit')
                ->name('schools.edit');
            Route::put('/schools/{school}', [SchoolController::class, 'update'])
                ->middleware('permission:school.edit')
                ->name('schools.update');
        });

        Route::middleware('permission:user.view')->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->middleware('permission:user.create')->name('create');
            Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create')->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware('permission:user.edit')->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:user.edit')->name('update');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:user.reset_password')->name('reset-password');
            Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->middleware('permission:user.toggle_active')->name('toggle-active');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete')->name('destroy');
        });

        // ─── Special Permissions ────────────────────────────────────────────
        Route::middleware('permission:settings.system_settings')->prefix('special-permissions')->name('special-permissions.')->group(function () {
            Route::get('/', [SpecialPermissionController::class, 'index'])->name('index');
            Route::post('/', [SpecialPermissionController::class, 'store'])->name('store');
            Route::post('/{exception}/revoke', [SpecialPermissionController::class, 'revoke'])->name('revoke');
        });

        // ─── Reports and Analytics ──────────────────────────────────────────
        Route::get('/reports-analytics', [ReportsAnalyticsController::class, 'index'])
            ->middleware('permission:report.enrollment')
            ->name('reports-analytics');

        Route::get('/reports/fee-collection', [ReportController::class, 'feeCollection'])
            ->middleware('permission:report.fee_collection')
            ->name('reports.fee-collection');

        Route::get('/reports/invoice-status', [ChallanApprovalController::class, 'pendingEnrollment'])
            ->middleware('permission:report.invoice_status')
            ->name('reports.invoice-status');

        Route::get('/districts', [ReportController::class, 'district'])
            ->middleware('permission:district.view')
            ->name('districts');

        // ─── Academic Year Control ──────────────────────────────────────────
        Route::middleware('permission:academicyear.view')->prefix('academic-years')->name('academic-years.')->group(function () {
            Route::get('/', [AcademicYearController::class, 'index'])->name('index');
            Route::post('/', [AcademicYearController::class, 'store'])
                ->middleware('permission:academicyear.create_next')
                ->name('store');
            Route::post('/{year}/activate', [AcademicYearController::class, 'setActive'])
                ->middleware('permission:academicyear.create_next')
                ->name('activate');
            Route::post('/{year}/enrollment/open', [AcademicYearController::class, 'openEnrollmentWindow'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('enrollment.open');
            Route::post('/{year}/enrollment/close', [AcademicYearController::class, 'closeEnrollmentWindow'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('enrollment.close');
            Route::post('/{year}/examination/open', [AcademicYearController::class, 'openExaminationWindow'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('examination.open');
            Route::post('/{year}/examination/close', [AcademicYearController::class, 'closeExaminationWindow'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('examination.close');
            Route::post('/{year}/examination/announce', [AcademicYearController::class, 'announceExamDates'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('examination.announce');
            Route::post('/{year}/examination/unannounce', [AcademicYearController::class, 'unannounceExamDates'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('examination.unannounce');
            Route::post('/{year}/window-dates', [AcademicYearController::class, 'updateWindowDates'])
                ->middleware('permission:academicyear.manage_windows')
                ->name('window-dates');
            Route::get('/create', [AcademicYearController::class, 'index'])
                ->middleware('permission:academicyear.create_next')
                ->name('create');
        });

        // ─── Window Overrides (district/school-level phase overrides) ──────
        Route::middleware('permission:academicyear.manage_windows')->prefix('window-overrides')->name('window-overrides.')->group(function () {
            Route::get('/', [WindowOverrideController::class, 'index'])->name('index');
            Route::post('/', [WindowOverrideController::class, 'store'])->name('store');
            Route::put('/{override}', [WindowOverrideController::class, 'update'])->name('update');
            Route::delete('/{override}', [WindowOverrideController::class, 'destroy'])->name('destroy');
        });

        Route::middleware(['permission:academicyear.create_next', 'throttle:3,1'])->group(function () {
            Route::get('/academic-year-transition', [AcademicYearTransitionController::class, 'show'])->name('academic-year-transition');
            Route::post('/academic-year-transition/promote', [AcademicYearTransitionController::class, 'promote'])->name('academic-year-transition.promote');
            Route::post('/academic-year-transition/expire', [AcademicYearTransitionController::class, 'expire'])->name('academic-year-transition.expire');
            Route::post('/academic-year-transition/swap', [AcademicYearTransitionController::class, 'swap'])->name('academic-year-transition.swap');
        });

        // ─── System and Audit ───────────────────────────────────────────────
        Route::get('/settings', fn () => redirect()->route('superadmin.settings.system'))->name('settings');
        Route::get('/settings/system', [SystemSettingsController::class, 'showSystem'])
            ->middleware('permission:settings.system_settings')
            ->name('settings.system');
        Route::post('/settings/system', [SystemSettingsController::class, 'updateSystem'])
            ->middleware('permission:settings.system_settings')
            ->name('settings.system.update');

        Route::get('/settings/notifications', [SystemSettingsController::class, 'showNotifications'])
            ->middleware('permission:settings.notifications')
            ->name('settings.notifications');
        Route::post('/settings/notifications', [SystemSettingsController::class, 'updateNotifications'])
            ->middleware('permission:settings.notifications')
            ->name('settings.notifications.update');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])
            ->middleware('permission:activitylog.view')
            ->name('activity-log');

        Route::middleware('permission:settings.notifications')->group(function () {
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        });
    });

Route::middleware(['auth', 'role.check:super_admin'])->prefix('admin')->group(function () {
    Route::redirect('/', '/superadmin/dashboard');
});
