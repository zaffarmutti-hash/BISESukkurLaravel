<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has the super_admin role.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'username' => 'Please log in to continue.',
            ]);
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'username' => 'Your account has been deactivated.',
            ]);
        }

        if (! $user->isSuperAdmin() && ! $user->hasRole('super_admin')) {
            return redirect()->route('login')->withErrors([
                'username' => 'You do not have permission to access the Super Admin portal.',
            ]);
        }

        return $next($request);
    }
}
