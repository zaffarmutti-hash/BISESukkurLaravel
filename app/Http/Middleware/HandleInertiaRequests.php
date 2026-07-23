<?php

namespace App\Http\Middleware;

use App\Services\BoardPolicyService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'username'   => $user->username,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'school_id'  => $user->school_id,
                    'district_id'=> $user->district_id,
                    'permissions'=> $user->getAllPermissions()->pluck('name'),
                    'school'     => $user->school ? [
                        'id'   => $user->school->id,
                        'name' => $user->school->name,
                        'code' => $user->school->code,
                        'username' => $user->school->username,
                    ] : null,
                ] : null,
            ],

            'activeYear' => fn () => $this->shareActiveYear($request),

            'boardPolicy' => fn () => $this->shareBoardPolicy($request),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],
        ];
    }

    protected function shareActiveYear(Request $request): ?array
    {
        $boardPolicy = app(BoardPolicyService::class);
        $year = $boardPolicy->activeYear();

        if (! $year) {
            return null;
        }

        $schoolId = $request->user()?->isSchoolAdmin()
            ? (int) ($request->attributes->get('school_scope_id') ?? $request->user()->school_id)
            : null;

        $enrollmentPhase  = $boardPolicy->enrollmentPhaseForSchool($schoolId);
        $examinationPhase = $boardPolicy->examinationPhaseForSchool($schoolId);

        return [
            'id'                      => $year->id,
            'label'                   => $year->label,

            // Legacy booleans (backward compatible)
            'enrollment_window_open'  => $year->enrollment_window_open,
            'examination_window_open' => $year->examination_window_open,

            // Backward-compatible access booleans
            'is_enrollment_open'      => $enrollmentPhase->isAccessAllowed,
            'is_examination_open'     => $examinationPhase->isAccessAllowed,

            // Three-phase details
            'enrollment_phase'        => $enrollmentPhase->toArray(),
            'examination_phase'       => $examinationPhase->toArray(),

            // Grace period dates
            'enrollment_grace_end'    => $year->enrollment_grace_end?->toIso8601String(),
            'examination_grace_end'   => $year->examination_grace_end?->toIso8601String(),
        ];
    }

    protected function shareBoardPolicy(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        $schoolId = $user->isSchoolAdmin()
            ? (int) ($request->attributes->get('school_scope_id') ?? $user->school_id)
            : null;

        return app(BoardPolicyService::class)->policySnapshotForSchool($schoolId);
    }
}
