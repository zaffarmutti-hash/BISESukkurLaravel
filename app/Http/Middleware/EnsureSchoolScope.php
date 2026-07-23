<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSchoolScope
 *
 * CRITICAL SECURITY MIDDLEWARE — Applied to every school.* route.
 *
 * This middleware injects the authenticated school admin's school_id into
 * the request as a shared constraint. Every controller in the School module
 * must honour this scope when querying the database.
 *
 * Even if a school admin guesses another school's student ID in a URL,
 * this middleware ensures that all queries will return zero rows for
 * records not belonging to their school — not a 403, not an error message,
 * just empty results, leaking nothing about the existence of the record.
 *
 * The school_id is NEVER read from form data or query strings.
 * It is ALWAYS read from the authenticated user's database record.
 */
class EnsureSchoolScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSchoolAdmin()) {
            abort(403, 'Access restricted to school administrators.');
        }

        if (! $user->school_id) {
            abort(403, 'Your account is not associated with any school. Contact the board office.');
        }

        if (! $user->school || ! $user->school->is_active) {
            abort(403, 'Your school account has been deactivated. Contact the board office.');
        }

        // Bind the school scope — controllers MUST use this value
        $request->merge(['_school_scope_id' => $user->school_id]);
        $request->attributes->set('school_scope_id', $user->school_id);
        $request->attributes->set('school_scope', $user->school);

        return $next($request);
    }
}
