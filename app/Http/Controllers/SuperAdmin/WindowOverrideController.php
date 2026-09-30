<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\School;
use App\Models\WindowOverride;
use Illuminate\Http\Request;

/**
 * CRUD for district-level and school-level window phase overrides.
 *
 * Each override fully replaces the global dates for one scope and one window type.
 * No partial merging — the override is self-contained.
 */
class WindowOverrideController extends Controller
{
    public function index(Request $request)
    {
        $year = AcademicYear::current();

        $overrides = $year
            ? WindowOverride::where('academic_year_id', $year->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($o) => $this->enrichOverride($o))
            : collect();

        return view('superadmin.settings.window_overrides', [
            'overrides'  => $overrides,
            'activeYear' => $year,
            'districts'  => District::orderBy('name')->get(['id', 'name']),
            'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
        ]);
    }

    public function store(Request $request)
    {
        $year = AcademicYear::current();
        if (! $year) {
            return back()->with('error', 'No active academic year configured.');
        }

        if (! $request->filled('scope_id')) {
            if ($request->input('scope_type') === 'district' && $request->filled('scope_id_district')) {
                $request->merge(['scope_id' => $request->input('scope_id_district')]);
            } elseif ($request->input('scope_type') === 'school' && $request->filled('scope_id_school')) {
                $request->merge(['scope_id' => $request->input('scope_id_school')]);
            }
        }

        $validated = $request->validate([
            'scope_type'   => 'required|in:district,school',
            'scope_id'     => 'required|integer',
            'window_type'  => 'required|in:enrollment,examination',
            'normal_start' => 'required|date',
            'normal_end'   => 'required|date|after:normal_start',
            'grace_end'    => 'nullable|date|after:normal_end',
        ]);

        // Validate scope_id exists
        if ($validated['scope_type'] === 'district') {
            District::findOrFail($validated['scope_id']);
        } else {
            School::findOrFail($validated['scope_id']);
        }

        // Check for existing active override (unique constraint)
        $existing = WindowOverride::where('academic_year_id', $year->id)
            ->where('scope_type', $validated['scope_type'])
            ->where('scope_id', $validated['scope_id'])
            ->where('window_type', $validated['window_type'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return back()->with('error', 'An active override already exists for this scope and window type. Edit or remove the existing one first.');
        }

        WindowOverride::create([
            ...$validated,
            'academic_year_id' => $year->id,
            'is_active'        => true,
        ]);

        $label = ucfirst($validated['scope_type']);
        return back()->with('success', "{$label}-level window override created. Takes effect immediately.");
    }

    public function update(Request $request, WindowOverride $override)
    {
        $validated = $request->validate([
            'normal_start' => 'required|date',
            'normal_end'   => 'required|date|after:normal_start',
            'grace_end'    => 'nullable|date|after:normal_end',
            'is_active'    => 'sometimes|boolean',
        ]);

        $override->update($validated);

        return back()->with('success', 'Window override updated. Changes take effect immediately.');
    }

    public function destroy(WindowOverride $override)
    {
        $override->delete();

        return back()->with('success', 'Window override removed. The scope will now follow the parent-level configuration.');
    }

    /**
     * Enrich an override record with the scope entity name for display.
     */
    private function enrichOverride(WindowOverride $override): array
    {
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
            'created_at'   => $override->created_at?->toIso8601String(),
        ];
    }
}
