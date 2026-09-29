<?php

namespace App\Contracts;

use App\Models\Invoice;
use Illuminate\Support\Collection;

interface IChallanService
{
    /**
     * Get distinct classes that have eligible students for the specified challan type and school.
     */
    public function getEligibleClasses(string $challanType, int $schoolId, int $activeYearId): array;

    /**
     * Get distinct groups that have eligible students for the specified class.
     */
    public function getEligibleGroups(string $challanType, int $schoolId, int $activeYearId, string $classLevel): array;

    /**
     * Get distinct student types that have eligible students for the specified class and group.
     */
    public function getEligibleStudentTypes(string $challanType, int $schoolId, int $activeYearId, string $classLevel, string $group): array;

    /**
     * Execute full eligibility query with exclusion subquery and previously-excluded flags.
     */
    public function getEligibleStudents(string $challanType, int $schoolId, int $activeYearId, string $classLevel, string $group, string $studentType): Collection;

    /**
     * Atomically generate an invoice/challan within a database transaction.
     *
     * @param array $studentSelections Array of ['id' => int, 'is_included' => bool]
     */
    public function generateChallan(
        string $challanType,
        int $schoolId,
        int $activeYearId,
        string $classLevel,
        string $group,
        string $studentType,
        float $feePerStudent,
        array $studentSelections,
        ?string $phase = null
    ): Invoice;
}
