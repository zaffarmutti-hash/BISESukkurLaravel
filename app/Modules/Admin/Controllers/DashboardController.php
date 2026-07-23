<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Challan;
use App\Models\Student;
use App\Models\School;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $activeYear = AcademicYear::current();

        // All stats are computed server-side, never trusted from browser
        $stats = [
            'active_year'                 => $activeYear?->label,
            'total_students_enrolled'     => 0, // TODO: fill in logic phase
            'students_by_district'        => [], // TODO: fill in logic phase
            'pending_enrollment_challans' => 0, // TODO: fill in logic phase
            'pending_exam_challans'       => 0, // TODO: fill in logic phase
            'students_awaiting_enroll_no' => 0, // TODO: fill in logic phase
            'students_without_exam_form'  => 0, // TODO: fill in logic phase
            'total_schools'               => 0, // TODO: fill in logic phase
        ];

        return Inertia::render('admin/Dashboard', [
            'stats'      => $stats,
            'activeYear' => $activeYear,
        ]);
    }
}
