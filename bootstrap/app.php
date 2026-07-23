<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSchoolScope;
use App\Http\Middleware\EnsureActiveYear;
use App\Http\Middleware\RedirectIfNotRole;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnrollmentWindowOpen;
use App\Http\Middleware\ExaminationWindowOpen;
use App\Http\Middleware\ForcePasswordChange;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Inertia middleware — must be in the web group
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Named middleware aliases
        // role.check     — gate on super_admin / school_admin role
        // school.scope   — CRITICAL: injects school_id from DB, never from browser
        // active.year    — injects current year from DB, never from browser
        // enrollment.window — blocks if enrollment window is closed
        // examination.window — blocks if examination window is closed
        // force.password — redirects to password change if first-login flag set
        $middleware->alias([
            'role.check'         => RedirectIfNotRole::class,
            'superadmin'         => EnsureSuperAdmin::class,
            'school.scope'       => EnsureSchoolScope::class,
            'active.year'        => EnsureActiveYear::class,
            'enrollment.window'  => EnrollmentWindowOpen::class,
            'examination.window' => ExaminationWindowOpen::class,
            'force.password'     => ForcePasswordChange::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Log all unhandled exceptions with request context
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Return proper JSON responses for API/AJAX requests
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Handle database connection failures gracefully
            if ($e instanceof \Illuminate\Database\QueryException ||
                $e instanceof \PDOException) {
                \Illuminate\Support\Facades\Log::critical('Database error', [
                    'message' => $e->getMessage(),
                    'url'     => $request->fullUrl(),
                    'user_id' => $request->user()?->id,
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'A database error occurred. Please try again or contact the board office.',
                    ], 503);
                }
            }

            // Handle 419 (CSRF token expired) — common with long-open forms
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please refresh the page and try again.',
                    ], 419);
                }

                return redirect()->back()
                    ->with('error', 'Your session expired. Please try again.');
            }
        });
    })->create();
