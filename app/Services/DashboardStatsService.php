<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\District;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DashboardStatsService
{
    public function totalStudents(?int $yearId = null): int
    {
        $yearId ??= AcademicYear::current()?->id;
        if (! $yearId) {
            return 0;
        }

        return StudentAcademicRecord::where('academic_year_id', $yearId)->count();
    }

    public function totalSchools(): int
    {
        return School::count();
    }

    public function schoolStats(): array
    {
        $active = School::where('is_active', true)->count();

        return [
            'total'    => School::count(),
            'active'   => $active,
            'inactive' => School::count() - $active,
        ];
    }

    public function pendingEnrollmentVerification(?int $yearId = null): int
    {
        $yearId ??= AcademicYear::current()?->id;

        return $yearId
            ? Invoice::pending()->where('academic_year_id', $yearId)->where('invoice_type', 'enrollment')->count()
            : Invoice::pending()->where('invoice_type', 'enrollment')->count();
    }

    public function pendingExamVerification(?int $yearId = null): int
    {
        $yearId ??= AcademicYear::current()?->id;

        return $yearId
            ? Invoice::pending()->where('academic_year_id', $yearId)->where('invoice_type', 'examination')->count()
            : Invoice::pending()->where('invoice_type', 'examination')->count();
    }

    public function pendingVerification(?int $yearId = null): int
    {
        return $this->pendingEnrollmentVerification($yearId) + $this->pendingExamVerification($yearId);
    }

    public function verifiedPaymentsAmount(?int $yearId = null): float
    {
        $yearId ??= AcademicYear::current()?->id;

        $paisas = $yearId
            ? Invoice::confirmed()->where('academic_year_id', $yearId)->sum('total_amount_paisas')
            : Invoice::confirmed()->sum('total_amount_paisas');

        return round($paisas / 100, 2);
    }

    public function pendingEnrollmentNumbers(?int $yearId = null): int
    {
        $yearId ??= AcademicYear::current()?->id;
        if (! $yearId) {
            return 0;
        }

        return Student::withoutEnrollmentNumber()
            ->whereHas('invoiceStudents.invoice', function ($q) use ($yearId) {
                $q->where('invoice_type', 'enrollment')
                    ->where('status', 'confirmed')
                    ->where('academic_year_id', $yearId);
            })
            ->count();
    }

    public function missingExamForms(?int $yearId = null): int
    {
        $yearId ??= AcademicYear::current()?->id;
        if (! $yearId) {
            return 0;
        }

        return Student::withEnrollmentNumber()
            ->whereDoesntHave('examForms', fn ($q) => $q->where('academic_year_id', $yearId))
            ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
            ->count();
    }

    public function districtAdminCount(): int
    {
        return User::where('role', 'district_admin')->where('is_active', true)->count();
    }

    public function systemHealth(): array
    {
        $issues = [];

        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
            $issues[] = 'Database connection failed';
        }

        try {
            Queue::size();
            $queueOk = true;
        } catch (\Throwable) {
            $queueOk = false;
            $issues[] = 'Queue unavailable';
        }

        $diskFree = @disk_free_space(storage_path());
        $diskTotal = @disk_total_space(storage_path());
        $diskOk = $diskFree && $diskTotal && ($diskFree / $diskTotal) > 0.05;
        if (! $diskOk) {
            $issues[] = 'Low disk space';
        }

        return [
            'healthy'    => empty($issues),
            'database'   => $dbOk,
            'queue'      => $queueOk,
            'disk'       => $diskOk,
            'disk_free'  => $diskFree ? round($diskFree / 1024 / 1024 / 1024, 2) : null,
            'issues'     => $issues,
        ];
    }

    public function districtBreakdown(?int $yearId = null): array
    {
        $yearId ??= AcademicYear::current()?->id;

        return District::withCount('schools')->get()->map(function (District $district) use ($yearId) {
            $schoolIds = School::where('district_id', $district->id)->pluck('id');

            $studentCount = $yearId
                ? StudentAcademicRecord::where('academic_year_id', $yearId)
                    ->whereHas('student', fn ($q) => $q->whereIn('school_id', $schoolIds))
                    ->count()
                : 0;

            $paisas = $yearId
                ? Invoice::confirmed()
                    ->where('academic_year_id', $yearId)
                    ->whereIn('school_id', $schoolIds)
                    ->sum('total_amount_paisas')
                : 0;
            $verifiedAmount = round($paisas / 100, 2);

            $pendingInvoices = $yearId
                ? Invoice::pending()
                    ->where('academic_year_id', $yearId)
                    ->whereIn('school_id', $schoolIds)
                    ->count()
                : 0;

            $missingExamForms = $yearId
                ? Student::withEnrollmentNumber()
                    ->whereIn('school_id', $schoolIds)
                    ->whereHas('academicRecords', fn ($q) => $q->where('academic_year_id', $yearId))
                    ->whereDoesntHave('examForms', fn ($q) => $q->where('academic_year_id', $yearId))
                    ->count()
                : 0;

            return [
                'id'                 => $district->id,
                'district_id'        => $district->id,
                'name'               => $district->name,
                'district_name'      => $district->name,
                'code'               => $district->code ?? ('#'.$district->id),
                'district_code'      => $district->code ?? ('#'.$district->id),
                'school_count'       => $district->schools_count,
                'student_count'      => $studentCount,
                'verified_amount'    => $verifiedAmount,
                'pending_invoices'   => $pendingInvoices,
                'missing_exam_forms' => $missingExamForms,
                'exam_gap'           => $missingExamForms,
            ];
        })->values()->all();
    }
}
