<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RedirectIfNotRole
 *
 * Usage: middleware('role.check:super_admin') or middleware('role.check:school_admin')
 *
 * Redirects users who are authenticated but do not have the required role.
 */
class RedirectIfNotRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== $role) {
            return redirect()->route('login')->withErrors([
                'username' => 'You do not have permission to access this area.',
            ]);
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'username' => 'Your account has been deactivated.',
            ]);
        }

        return $next($request);
    }
}
