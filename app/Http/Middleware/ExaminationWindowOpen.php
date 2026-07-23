<?php

namespace App\Http\Middleware;

use App\Services\BoardPolicyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Three-phase examination window gate.
 *
 * Allows requests during 'normal' and 'grace' phases.
 * Blocks requests during 'closed' phase (unless Super Admin).
 * Injects the resolved phase into request attributes for downstream controllers.
 */
class ExaminationWindowOpen
{
    public function __construct(private BoardPolicyService $boardPolicy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin is never blocked
        if ($user?->isSuperAdmin()) {
            $request->attributes->set('examination_phase', 'normal');
            return $next($request);
        }

        $schoolId = $user?->isSchoolAdmin()
            ? (int) $request->attributes->get('school_scope_id', $user->school_id)
            : null;

        $phaseResult = $this->boardPolicy->examinationPhaseForSchool($schoolId);

        // Inject the resolved phase for downstream use (challan generation, etc.)
        $request->attributes->set('examination_phase', $phaseResult->phase);
        $request->attributes->set('examination_phase_result', $phaseResult);

        if ($phaseResult->isAccessAllowed) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return back()->with('error', 'The board examination window is currently closed. Contact the board office if you need an extension.');
        }

        abort(403, 'The board examination window is currently closed.');
    }
}
