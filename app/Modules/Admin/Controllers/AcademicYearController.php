<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\WindowOverride;
use App\Models\District;
use App\Models\School;
use App\Services\WindowPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Board-level academic year management.
 * Window toggles, phase dates, and active year selection apply to every school immediately.
 *
 * The three-phase window system:
 *   normal_start → normal_end → grace_end (optional) → closed
 */
class AcademicYearController extends Controller
{
    public function index(Request $request): Response
    {
        $years = AcademicYear::orderByDesc('year_start')->get();
        $routeName = $request->route()?->getName() ?? '';

        if ($this->isWindowSettingsRoute($routeName)) {
            return Inertia::render('admin/enrollment/WindowSettings', [
                'years'      => $years,
                'actions'    => $this->yearActions($request),
                'overrides'  => $this->getOverridesForActiveYear(),
                'districts'  => District::orderBy('name')->get(['id', 'name']),
                'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
            ]);
        }

        return Inertia::render('admin/years/Index', [
            'years'   => $years,
            'actions' => $this->yearActions($request),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:20|unique:academic_years,label',
            'year_start' => 'required|integer|min:2000|max:2100',
            'year_end' => 'required|integer|gt:year_start',
            'enrollment_open_date' => 'required|date',
            'enrollment_close_date' => 'required|date|after:enrollment_open_date',
            'enrollment_grace_end' => 'nullable|date|after:enrollment_close_date',
            'examination_open_date' => 'required|date',
            'examination_close_date' => 'required|date|after:examination_open_date',
            'examination_grace_end' => 'nullable|date|after:examination_close_date',
        ]);

        $validated['label'] ??= "{$validated['year_start']}-{$validated['year_end']}";
        $validated['is_active'] = false;
        $validated['enrollment_window_open'] = false;
        $validated['examination_window_open'] = false;

        AcademicYear::create($validated);

        return back()->with('success', 'Academic year created. Activate it when ready — all schools follow the active year.');
    }

    public function setActive(AcademicYear $year)
    {
        DB::transaction(function () use ($year) {
            AcademicYear::query()->update(['is_active' => false]);
            $year->update(['is_active' => true]);
        });

        return back()->with('success', 'Active academic year updated for all schools.');
    }

    public function openEnrollmentWindow(AcademicYear $year)
    {
        $year->update(['enrollment_window_open' => true]);

        return back()->with('success', 'Enrollment window opened for all schools.');
    }

    public function closeEnrollmentWindow(AcademicYear $year)
    {
        $year->update(['enrollment_window_open' => false]);

        return back()->with('success', 'Enrollment window closed for all schools.');
    }

    public function openExaminationWindow(AcademicYear $year)
    {
        $year->update(['examination_window_open' => true]);

        return back()->with('success', 'Examination window opened for all schools.');
    }

    public function closeExaminationWindow(AcademicYear $year)
    {
        $year->update(['examination_window_open' => false]);

        return back()->with('success', 'Examination window closed for all schools.');
    }

    /**
     * Update window dates including optional grace period for a specific window type.
     */
    public function updateWindowDates(Request $request, AcademicYear $year)
    {
        $windowType = $request->input('window_type', 'enrollment');

        if ($windowType === 'enrollment') {
            $validated = $request->validate([
                'enrollment_open_date'  => 'required|date',
                'enrollment_close_date' => 'required|date|after:enrollment_open_date',
                'enrollment_grace_end'  => 'nullable|date|after:enrollment_close_date',
            ]);
        } else {
            $validated = $request->validate([
                'examination_open_date'  => 'required|date',
                'examination_close_date' => 'required|date|after:examination_open_date',
                'examination_grace_end'  => 'nullable|date|after:examination_close_date',
            ]);
        }

        $year->update($validated);

        $label = ucfirst($windowType);
        return back()->with('success', "{$label} window dates updated. Changes take effect immediately for all schools.");
    }

    public function announceExamDates(AcademicYear $year)
    {
        $year->update(['exam_dates_announced' => true]);

        return back()->with('success', 'Examination dates announced and published.');
    }

    public function unannounceExamDates(AcademicYear $year)
    {
        $year->update(['exam_dates_announced' => false]);

        return back()->with('success', 'Examination dates announcement withdrawn.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function isWindowSettingsRoute(string $routeName): bool
    {
        return str_contains($routeName, 'enrollment-windows')
            || str_contains($routeName, 'exam-windows')
            || str_contains($routeName, 'fee-windows');
    }

    private function yearActions(Request $request): array
    {
        $isSuperAdmin = str_starts_with($request->route()?->getName() ?? '', 'superadmin.');
        $prefix = $isSuperAdmin ? 'superadmin.academic-years' : 'admin.years';

        return [
            'store'              => route("{$prefix}.store"),
            'activate'           => route("{$prefix}.activate", ['year' => '__ID__']),
            'enrollment_open'    => route("{$prefix}.enrollment.open", ['year' => '__ID__']),
            'enrollment_close'   => route("{$prefix}.enrollment.close", ['year' => '__ID__']),
            'examination_open'   => route("{$prefix}.examination.open", ['year' => '__ID__']),
            'examination_close'  => route("{$prefix}.examination.close", ['year' => '__ID__']),
            'exam_announce'      => route("{$prefix}.examination.announce", ['year' => '__ID__']),
            'exam_unannounce'    => route("{$prefix}.examination.unannounce", ['year' => '__ID__']),
            'update_window_dates'=> route("{$prefix}.window-dates", ['year' => '__ID__']),
        ];
    }

    private function getOverridesForActiveYear(): array
    {
        $year = AcademicYear::current();
        if (! $year) {
            return [];
        }

        $phaseService = new WindowPhaseService();

        return WindowOverride::where('academic_year_id', $year->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($override) use ($phaseService) {
                $scopeName = $override->scope_type === 'district'
                    ? District::find($override->scope_id)?->name
                    : School::find($override->scope_id)?->name;

                return [
                    'id'           => $override->id,
                    'scope_type'   => $override->scope_type,
                    'scope_id'     => $override->scope_id,
                    'scope_name'   => $scopeName ?? 'Unknown',
                    'window_type'  => $override->window_type,
                    'normal_start' => $override->normal_start?->toIso8601String(),
                    'normal_end'   => $override->normal_end?->toIso8601String(),
                    'grace_end'    => $override->grace_end?->toIso8601String(),
                    'has_grace'    => $override->hasGracePeriod(),
                    'is_active'    => $override->is_active,
                ];
            })
            ->values()
            ->all();
    }
}
