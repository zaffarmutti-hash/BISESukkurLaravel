<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolException;
use App\Models\WindowOverride;
use Carbon\Carbon;

/**
 * Central three-phase window evaluation service.
 *
 * Resolves which phase (normal, grace, closed) a specific school is currently
 * in for a given window type, following the precedence hierarchy:
 *   school-level override → district-level override → global AcademicYear dates
 *
 * CRITICAL: This service evaluates live on every request. No caching.
 */
class WindowPhaseService
{
    /**
     * Resolve the current phase for a school and window type.
     *
     * @param  int|null  $schoolId    Null for global-only evaluation
     * @param  string    $windowType  'enrollment' or 'examination'
     * @return WindowPhaseResult
     */
    public function resolvePhase(?int $schoolId, string $windowType): WindowPhaseResult
    {
        $year = AcademicYear::current();

        if (! $year) {
            return WindowPhaseResult::closed();
        }

        // Master kill-switch check — if the boolean is false, force closed
        $killSwitch = $windowType === 'enrollment'
            ? $year->enrollment_window_open
            : $year->examination_window_open;

        if (! $killSwitch) {
            // Even with kill-switch off, check for school exception
            if ($schoolId && $this->schoolHasException($schoolId, $year->id, $windowType)) {
                return $this->buildExceptionResult($year, $windowType);
            }

            return WindowPhaseResult::closed();
        }

        // Resolve dates using the override hierarchy
        $boundaries = $this->resolveBoundaries($schoolId, $year, $windowType);

        if (! $boundaries) {
            return WindowPhaseResult::closed();
        }

        $now = Carbon::now();

        // Evaluate phase against boundaries
        $result = $this->evaluatePhase($now, $boundaries);

        // If closed by dates but school has an exception, treat as grace
        if ($result->phase === 'closed' && $schoolId) {
            if ($this->schoolHasException($schoolId, $year->id, $windowType)) {
                return new WindowPhaseResult(
                    phase: 'grace',
                    normalStart: $boundaries['normal_start'],
                    normalEnd: $boundaries['normal_end'],
                    graceEnd: $boundaries['grace_end'],
                    isAccessAllowed: true,
                    nextTransition: null,
                    nextTransitionLabel: 'Access via special permission',
                    overrideLevel: $result->overrideLevel,
                    isExceptionBased: true,
                );
            }
        }

        return $result;
    }

    /**
     * Resolve the boundaries (dates) from the appropriate scope level.
     *
     * Precedence: school → district → global.
     * Each level fully replaces the one above — no partial merging.
     *
     * @return array{normal_start: Carbon|null, normal_end: Carbon|null, grace_end: Carbon|null, override_level: string}|null
     */
    protected function resolveBoundaries(?int $schoolId, AcademicYear $year, string $windowType): ?array
    {
        // 1. School-level override
        if ($schoolId) {
            $schoolOverride = WindowOverride::query()
                ->where('academic_year_id', $year->id)
                ->where('scope_type', 'school')
                ->where('scope_id', $schoolId)
                ->where('window_type', $windowType)
                ->where('is_active', true)
                ->first();

            if ($schoolOverride) {
                return [
                    'normal_start'   => $schoolOverride->normal_start,
                    'normal_end'     => $schoolOverride->normal_end,
                    'grace_end'      => $schoolOverride->grace_end,
                    'override_level' => 'school',
                ];
            }

            // 2. District-level override (lookup the school's district)
            $school = School::find($schoolId);
            if ($school && $school->district_id) {
                $districtOverride = WindowOverride::query()
                    ->where('academic_year_id', $year->id)
                    ->where('scope_type', 'district')
                    ->where('scope_id', $school->district_id)
                    ->where('window_type', $windowType)
                    ->where('is_active', true)
                    ->first();

                if ($districtOverride) {
                    return [
                        'normal_start'   => $districtOverride->normal_start,
                        'normal_end'     => $districtOverride->normal_end,
                        'grace_end'      => $districtOverride->grace_end,
                        'override_level' => 'district',
                    ];
                }
            }
        }

        // 3. Global level (AcademicYear)
        $normalStart = $windowType === 'enrollment'
            ? ($year->enrollment_open_date ? Carbon::parse($year->enrollment_open_date)->startOfDay() : null)
            : ($year->examination_open_date ? Carbon::parse($year->examination_open_date)->startOfDay() : null);

        $normalEnd = $windowType === 'enrollment'
            ? ($year->enrollment_close_date ? Carbon::parse($year->enrollment_close_date)->endOfDay() : null)
            : ($year->examination_close_date ? Carbon::parse($year->examination_close_date)->endOfDay() : null);

        $graceEnd = $windowType === 'enrollment'
            ? $year->enrollment_grace_end
            : $year->examination_grace_end;

        if (! $normalStart || ! $normalEnd) {
            return null;
        }

        return [
            'normal_start'   => $normalStart,
            'normal_end'     => $normalEnd,
            'grace_end'      => $graceEnd,
            'override_level' => 'global',
        ];
    }

    /**
     * Evaluate which phase the current time falls into given the boundaries.
     */
    protected function evaluatePhase(Carbon $now, array $boundaries): WindowPhaseResult
    {
        $normalStart = $boundaries['normal_start'];
        $normalEnd   = $boundaries['normal_end'];
        $graceEnd    = $boundaries['grace_end'];
        $level       = $boundaries['override_level'];

        // Before normal start → closed
        if ($now->lt($normalStart)) {
            return new WindowPhaseResult(
                phase: 'closed',
                normalStart: $normalStart,
                normalEnd: $normalEnd,
                graceEnd: $graceEnd,
                isAccessAllowed: false,
                nextTransition: $normalStart,
                nextTransitionLabel: 'Opens ' . $normalStart->diffForHumans(),
                overrideLevel: $level,
            );
        }

        // Within normal period
        if ($now->lte($normalEnd)) {
            $nextBoundary = $graceEnd ? $normalEnd : $normalEnd;
            $nextLabel = $graceEnd
                ? 'Grace period begins ' . $normalEnd->diffForHumans()
                : 'Closes ' . $normalEnd->diffForHumans();

            return new WindowPhaseResult(
                phase: 'normal',
                normalStart: $normalStart,
                normalEnd: $normalEnd,
                graceEnd: $graceEnd,
                isAccessAllowed: true,
                nextTransition: $normalEnd,
                nextTransitionLabel: $nextLabel,
                overrideLevel: $level,
            );
        }

        // Within grace period (if configured)
        if ($graceEnd && $now->lte($graceEnd)) {
            return new WindowPhaseResult(
                phase: 'grace',
                normalStart: $normalStart,
                normalEnd: $normalEnd,
                graceEnd: $graceEnd,
                isAccessAllowed: true,
                nextTransition: $graceEnd,
                nextTransitionLabel: 'Closes ' . $graceEnd->diffForHumans(),
                overrideLevel: $level,
            );
        }

        // After everything → closed
        return new WindowPhaseResult(
            phase: 'closed',
            normalStart: $normalStart,
            normalEnd: $normalEnd,
            graceEnd: $graceEnd,
            isAccessAllowed: false,
            nextTransition: null,
            nextTransitionLabel: 'Window has closed',
            overrideLevel: $level,
        );
    }

    /**
     * Build a result for exception-based access (treated as grace phase).
     */
    protected function buildExceptionResult(AcademicYear $year, string $windowType): WindowPhaseResult
    {
        $normalStart = $windowType === 'enrollment'
            ? ($year->enrollment_open_date ? Carbon::parse($year->enrollment_open_date)->startOfDay() : null)
            : ($year->examination_open_date ? Carbon::parse($year->examination_open_date)->startOfDay() : null);

        $normalEnd = $windowType === 'enrollment'
            ? ($year->enrollment_close_date ? Carbon::parse($year->enrollment_close_date)->endOfDay() : null)
            : ($year->examination_close_date ? Carbon::parse($year->examination_close_date)->endOfDay() : null);

        return new WindowPhaseResult(
            phase: 'grace',
            normalStart: $normalStart,
            normalEnd: $normalEnd,
            graceEnd: null,
            isAccessAllowed: true,
            nextTransition: null,
            nextTransitionLabel: 'Access via special permission',
            overrideLevel: 'global',
            isExceptionBased: true,
        );
    }

    /**
     * Check if a school has an active deadline-extension exception.
     */
    protected function schoolHasException(int $schoolId, int $yearId, string $windowType): bool
    {
        $exceptionType = $windowType === 'enrollment'
            ? 'extend_enrollment_deadline'
            : 'extend_examination_deadline';

        return SchoolException::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->where('exception_type', $exceptionType)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}

/**
 * Value object representing the resolved window phase for a specific scope.
 */
class WindowPhaseResult
{
    public function __construct(
        public readonly string  $phase,             // 'normal', 'grace', 'closed'
        public readonly ?Carbon $normalStart,
        public readonly ?Carbon $normalEnd,
        public readonly ?Carbon $graceEnd,
        public readonly bool    $isAccessAllowed,
        public readonly ?Carbon $nextTransition,
        public readonly string  $nextTransitionLabel,
        public readonly string  $overrideLevel = 'global',  // 'global', 'district', 'school'
        public readonly bool    $isExceptionBased = false,
    ) {}

    public static function closed(): self
    {
        return new self(
            phase: 'closed',
            normalStart: null,
            normalEnd: null,
            graceEnd: null,
            isAccessAllowed: false,
            nextTransition: null,
            nextTransitionLabel: 'No active year configured',
        );
    }

    /**
     * Serialize for Inertia/JSON transport.
     */
    public function toArray(): array
    {
        return [
            'phase'                  => $this->phase,
            'is_access_allowed'      => $this->isAccessAllowed,
            'normal_start'           => $this->normalStart?->toIso8601String(),
            'normal_end'             => $this->normalEnd?->toIso8601String(),
            'grace_end'              => $this->graceEnd?->toIso8601String(),
            'next_transition'        => $this->nextTransition?->toIso8601String(),
            'next_transition_label'  => $this->nextTransitionLabel,
            'override_level'         => $this->overrideLevel,
            'is_exception_based'     => $this->isExceptionBased,
        ];
    }
}
