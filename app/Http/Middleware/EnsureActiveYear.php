<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureActiveYear
 *
 * Resolves the currently active academic year from the DATABASE on every request
 * where an active year is required. The active year is NEVER read from the
 * request, form data, cookies, or any browser-supplied value.
 *
 * Injects the active year into the request attributes so all controllers
 * can reliably read it without hitting the database again (cached per request).
 */
class EnsureActiveYear
{
    public function handle(Request $request, Closure $next): Response
    {
        $activeYear = AcademicYear::current();

        if (! $activeYear) {
            // Super Admin needs to set an active year before any operations can proceed
            if ($request->user()?->isSuperAdmin()) {
                $request->attributes->set('active_year', null);
                return $next($request);
            }

            abort(503, 'No active academic year is configured. Please contact the board office.');
        }

        // Inject into request attributes — never from request input
        $request->attributes->set('active_year', $activeYear);

        return $next($request);
    }
}
