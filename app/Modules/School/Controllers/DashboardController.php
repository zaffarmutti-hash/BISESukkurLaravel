<?php

namespace App\Modules\School\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesSchoolScope;
use App\Services\BoardPolicyService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesSchoolScope;

    public function __construct(private BoardPolicyService $boardPolicy) {}

    public function index(): Response
    {
        $schoolId = $this->schoolScopeId();
        $boardPolicy = $this->boardPolicy->policySnapshotForSchool($schoolId);

        $stats = [
            'active_year' => $boardPolicy['label'] ?? null,
            'total_students' => 0,
            'pending_enrollment_challans' => 0,
            'pending_exam_forms' => 0,
        ];

        return Inertia::render('school/Dashboard', [
            'stats'      => $stats,
            'activeYear' => $boardPolicy,
        ]);
    }
}
