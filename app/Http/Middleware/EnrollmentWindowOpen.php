<?php

namespace App\Http\Middleware;

use App\Services\BoardPolicyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Three-phase enrollment window gate.
 *
 * Allows requests during 'normal' and 'grace' phases.
 * Blocks requests during 'closed' phase (unless Super Admin).
 * Injects the resolved phase into request attributes for downstream controllers.
 */
class EnrollmentWindowOpen
{
    public function __construct(private BoardPolicyService $boardPolicy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin is never blocked
        if ($user?->isSuperAdmin()) {
            $request->attributes->set('enrollment_phase', 'normal');
            return $next($request);
        }

        $schoolId = $user?->isSchoolAdmin()
            ? (int) $request->attributes->get('school_scope_id', $user->school_id)
            : null;

        $phaseResult = $this->boardPolicy->enrollmentPhaseForSchool($schoolId);

        // Inject the resolved phase for downstream use (challan generation, etc.)
        $request->attributes->set('enrollment_phase', $phaseResult->phase);
        $request->attributes->set('enrollment_phase_result', $phaseResult);

        if ($phaseResult->isAccessAllowed) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $request->isMethodSafe()) {
            return back()->with('error', 'The board enrollment window is currently closed. Contact the board office if you need an extension.');
        }

        abort(403, 'The board enrollment window is currently closed.');
    }
}
