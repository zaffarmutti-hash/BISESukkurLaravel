<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\SchoolException;
use Illuminate\Support\Collection;

/**
 * Central board policy — single source of truth for settings that apply to ALL schools.
 *
 * Schools (tenants) only hold their own operational data (students, challans, etc.).
 * Academic years, fee rates, and window toggles live here at board level.
 *
 * Three-phase window evaluation is delegated to WindowPhaseService.
 */
class BoardPolicyService
{
    private WindowPhaseService $windowPhaseService;

    public function __construct()
    {
        $this->windowPhaseService = new WindowPhaseService();
    }

    public function activeYear(): ?AcademicYear
    {
        return AcademicYear::current();
    }

    // ─── Three-Phase Window Evaluation ────────────────────────────────────────

    /**
     * Resolve the full phase result for a school's enrollment window.
     */
    public function enrollmentPhaseForSchool(?int $schoolId = null): WindowPhaseResult
    {
        return $this->windowPhaseService->resolvePhase($schoolId, 'enrollment');
    }

    /**
     * Resolve the full phase result for a school's examination window.
     */
    public function examinationPhaseForSchool(?int $schoolId = null): WindowPhaseResult
    {
        return $this->windowPhaseService->resolvePhase($schoolId, 'examination');
    }

    /**
     * Backward-compatible: is enrollment access allowed (normal or grace)?
     */
    public function isEnrollmentOpenForSchool(?int $schoolId = null): bool
    {
        return $this->enrollmentPhaseForSchool($schoolId)->isAccessAllowed;
    }

    /**
     * Backward-compatible: is examination access allowed (normal or grace)?
     */
    public function isExaminationOpenForSchool(?int $schoolId = null): bool
    {
        return $this->examinationPhaseForSchool($schoolId)->isAccessAllowed;
    }

    /**
     * @deprecated Use enrollmentPhaseForSchool() instead
     */
    public function yearEnrollmentWindowOpen(AcademicYear $year): bool
    {
        return (bool) $year->enrollment_window_open;
    }

    /**
     * @deprecated Use examinationPhaseForSchool() instead
     */
    public function yearExaminationWindowOpen(AcademicYear $year): bool
    {
        return (bool) $year->examination_window_open;
    }

    // ─── Fee Resolution ───────────────────────────────────────────────────────

    public function activeFeeStructures(?int $yearId = null): Collection
    {
        $yearId ??= $this->activeYear()?->id;
        if (! $yearId) {
            return collect();
        }

        return FeeStructure::query()
            ->where('academic_year_id', $yearId)
            ->where('is_active', true)
            ->orderBy('class_level')
            ->orderBy('student_type')
            ->orderBy('fee_type')
            ->get();
    }

    public function allFeeStructures(?int $yearId = null): Collection
    {
        $yearId ??= $this->activeYear()?->id;
        if (! $yearId) {
            return collect();
        }

        return FeeStructure::query()
            ->where('academic_year_id', $yearId)
            ->orderBy('class_level')
            ->orderBy('student_type')
            ->orderBy('fee_type')
            ->get();
    }

    public function findFee(
        string $classLevel,
        string $studentType,
        string $feeType,
        ?int $yearId = null
    ): ?FeeStructure {
        $yearId ??= $this->activeYear()?->id;
        if (! $yearId) {
            return null;
        }

        return FeeStructure::query()
            ->where('academic_year_id', $yearId)
            ->where('class_level', $classLevel)
            ->where('student_type', $studentType)
            ->where('fee_type', $feeType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Snapshot shared with Inertia so school portals reflect live board policy.
     * Now includes three-phase window details and late fee information.
     */
    public function policySnapshotForSchool(?int $schoolId = null): ?array
    {
        $year = $this->activeYear();
        if (! $year) {
            return null;
        }

        $enrollmentPhase  = $this->enrollmentPhaseForSchool($schoolId);
        $examinationPhase = $this->examinationPhaseForSchool($schoolId);

        return [
            'academic_year_id'        => $year->id,
            'label'                   => $year->label,

            // Legacy boolean fields (kept for backward compatibility)
            'enrollment_window_open'  => $year->enrollment_window_open,
            'examination_window_open' => $year->examination_window_open,

            // Backward-compatible booleans
            'is_enrollment_open'      => $enrollmentPhase->isAccessAllowed,
            'is_examination_open'     => $examinationPhase->isAccessAllowed,

            // Three-phase details
            'enrollment_phase'        => $enrollmentPhase->toArray(),
            'examination_phase'       => $examinationPhase->toArray(),

            // Date display
            'enrollment_open_date'    => $year->enrollment_open_date?->toDateString(),
            'enrollment_close_date'   => $year->enrollment_close_date?->toDateString(),
            'enrollment_grace_end'    => $year->enrollment_grace_end?->toIso8601String(),
            'examination_open_date'   => $year->examination_open_date?->toDateString(),
            'examination_close_date'  => $year->examination_close_date?->toDateString(),
            'examination_grace_end'   => $year->examination_grace_end?->toIso8601String(),

            // Exception status
            'has_enrollment_exception' => $schoolId && $this->schoolHasException($schoolId, $year->id, 'extend_enrollment_deadline'),
            'has_examination_exception'=> $schoolId && $this->schoolHasException($schoolId, $year->id, 'extend_examination_deadline'),
        ];
    }

    protected function schoolHasException(int $schoolId, int $yearId, string $type): bool
    {
        return SchoolException::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->where('exception_type', $type)
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    public function resolveFee(
        string $classLevel,
        string $studentType,
        string $feeType,
        ?int $yearId = null
    ): FeeStructure {
        $mappedType = match (strtolower(trim($studentType))) {
            'regular' => 'fresh',
            default => strtolower(trim($studentType)),
        };

        $mappedClass = match (strtolower(trim($classLevel))) {
            'matric' => 'ssc_part1',
            'intermediate' => 'hsc_part1',
            default => strtolower(trim($classLevel)),
        };

        $fee = $this->findFee($mappedClass, $mappedType, $feeType, $yearId);

        if (!$fee) {
            throw new \RuntimeException(
                "Fee structure not configured for Class: {$classLevel} (mapped: {$mappedClass}), " .
                "Type: {$studentType} (mapped: {$mappedType}), Fee Type: {$feeType}."
            );
        }

        return $fee;
    }

    /**
     * Resolve the fee amount in paisas, applying late fee surcharge if in grace phase.
     */
    public function resolveFeeAmountPaisas(
        string $classLevel,
        string $studentType,
        string $feeType,
        ?int $yearId = null,
        string $phase = 'normal'
    ): int {
        $fee = $this->resolveFee($classLevel, $studentType, $feeType, $yearId);

        if ($phase === 'grace') {
            return $fee->amount_paisas + ($fee->late_fee_surcharge_paisas ?? 0);
        }

        return $fee->amount_paisas;
    }

    /**
     * Get the late fee surcharge details for a given fee combination.
     * Useful for showing the school admin what the surcharge will be before confirming.
     */
    public function getLateFeeDetails(
        string $classLevel,
        string $studentType,
        string $feeType,
        ?int $yearId = null
    ): array {
        $fee = $this->resolveFee($classLevel, $studentType, $feeType, $yearId);

        return [
            'base_amount_paisas'      => $fee->amount_paisas,
            'surcharge_paisas'        => $fee->late_fee_surcharge_paisas ?? 0,
            'grace_total_paisas'      => $fee->grace_total_paisas,
            'base_amount_rupees'      => number_format($fee->amount_paisas / 100, 2),
            'surcharge_rupees'        => number_format(($fee->late_fee_surcharge_paisas ?? 0) / 100, 2),
            'grace_total_rupees'      => number_format($fee->grace_total_paisas / 100, 2),
        ];
    }

    /**
     * Expand UI class aliases to DB class_level values.
     *
     * @return string[]
     */
    public function expandClassLevels(string $classLevel): array
    {
        return match (strtolower(trim($classLevel))) {
            'matric' => ['ssc_part1', 'ssc_part2'],
            'intermediate' => ['hsc_part1', 'hsc_part2'],
            default => [strtolower(trim($classLevel))],
        };
    }
}
