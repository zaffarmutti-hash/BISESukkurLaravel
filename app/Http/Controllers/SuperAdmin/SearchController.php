<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Global debounced search endpoint.
     * Searches schools, students, invoices, users, certificates, and portal navigation.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([
                'schools'      => [],
                'students'     => [],
                'invoices'     => [],
                'users'        => [],
                'certificates' => [],
                'navigation'   => [],
            ]);
        }

        try {
            // ── 1. Schools (by name, semis_code, username, principal_name) ──────
            $schools = School::where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                    ->orWhere('semis_code', 'ilike', "%{$q}%")
                    ->orWhere('username', 'ilike', "%{$q}%")
                    ->orWhere('principal_name', 'ilike', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'username', 'semis_code', 'type'])
            ->map(fn ($s) => [
                'id'       => $s->id,
                'title'    => $s->name,
                'subtitle' => "SEMIS: {$s->semis_code} | Code: {$s->username}",
                'url'      => route('superadmin.schools.show', $s->id),
                'badge'    => strtoupper($s->type ?? 'SCHOOL'),
            ]);

            // ── 2. Students (by full_name, father_name, cnic, enrollment_number) ─
            $students = Student::where(function ($query) use ($q) {
                $query->where('full_name', 'ilike', "%{$q}%")
                    ->orWhere('father_name', 'ilike', "%{$q}%")
                    ->orWhere('cnic', 'like', "%{$q}%")
                    ->orWhere('enrollment_number', 'ilike', "%{$q}%");
            })
            ->with('school:id,name')
            ->limit(5)
            ->get(['id', 'full_name', 'father_name', 'enrollment_number', 'school_id'])
            ->map(fn ($st) => [
                'id'       => $st->id,
                'title'    => $st->full_name . ($st->father_name ? " s/d of {$st->father_name}" : ''),
                'subtitle' => ($st->enrollment_number ? "Enr: {$st->enrollment_number} | " : 'Pending Enr | ') . ($st->school?->name ?? 'Board Candidate'),
                'url'      => route('superadmin.enrollment.allotment') . "?search=" . urlencode($st->enrollment_number ?? $st->full_name),
                'badge'    => $st->enrollment_number ? 'ENROLLED' : 'CANDIDATE',
            ]);

            // ── 3. Invoices (by invoice_number) ─────────────────────────────────
            $invoices = Invoice::where('invoice_number', 'ilike', "%{$q}%")
                ->with('school:id,name')
                ->limit(5)
                ->get(['id', 'invoice_number', 'invoice_type', 'status', 'total_amount_paisas', 'school_id'])
                ->map(fn ($inv) => [
                    'id'       => $inv->id,
                    'title'    => $inv->invoice_number,
                    'subtitle' => "Rs " . number_format(($inv->total_amount_paisas ?? 0) / 100) . " | " . ($inv->school?->name ?? 'Board Challan'),
                    'url'      => route('superadmin.invoices.verify') . "?invoice=" . urlencode($inv->invoice_number),
                    'badge'    => strtoupper($inv->status ?? 'CHALLAN'),
                ]);

            // ── 4. Users (by username, name, email) ─────────────────────────────
            $users = User::where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                    ->orWhere('username', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'username', 'role'])
            ->map(fn ($u) => [
                'id'       => $u->id,
                'title'    => $u->name,
                'subtitle' => "Username: {$u->username} | Role: " . ucwords(str_replace('_', ' ', $u->role ?? 'User')),
                'url'      => route('superadmin.users.edit', $u->id),
                'badge'    => strtoupper(str_replace('_', ' ', $u->role ?? 'USER')),
            ]);

            // ── 5. Certificates (by verification_token) ─────────────────────────
            $certificates = Certificate::where('verification_token', 'ilike', "%{$q}%")
                ->with(['student:id,full_name', 'academicYear:id,name'])
                ->limit(5)
                ->get(['id', 'verification_token', 'level', 'student_id', 'academic_year_id'])
                ->map(fn ($c) => [
                    'id'       => $c->id,
                    'title'    => "Certificate #{$c->verification_token}",
                    'subtitle' => ($c->student?->full_name ?? 'Candidate') . " | " . strtoupper($c->level ?? 'SSC'),
                    'url'      => url("/verify/{$c->verification_token}"),
                    'badge'    => 'CERTIFICATE',
                ]);

            // ── 6. Navigation static index matching query ───────────────────────
            $navDirectory = [
                ['title' => 'Dashboard', 'subtitle' => 'Command Center & Analytics', 'url' => route('superadmin.dashboard'), 'badge' => 'OVERVIEW', 'keywords' => 'overview kpi command analytics home summary'],
                ['title' => 'System Health', 'subtitle' => 'Server & database diagnostics', 'url' => route('superadmin.health'), 'badge' => 'OVERVIEW', 'keywords' => 'health diagnostics db server storage queue failed jobs'],
                ['title' => 'Schools Directory', 'subtitle' => 'Manage registered institutions', 'url' => route('superadmin.schools'), 'badge' => 'MANAGEMENT', 'keywords' => 'schools institutions colleges registration semis institutes'],
                ['title' => 'Districts Overview', 'subtitle' => '5 Sindh districts jurisdiction', 'url' => route('superadmin.districts'), 'badge' => 'MANAGEMENT', 'keywords' => 'districts sukkur khairpur ghotki noshero feroze jurisdiction'],
                ['title' => 'Users & Staff', 'subtitle' => 'Board officers & roles', 'url' => route('superadmin.users.index'), 'badge' => 'MANAGEMENT', 'keywords' => 'users roles staff controllers operators accounts permissions'],
                ['title' => 'Board Announcements', 'subtitle' => 'Circulars and public broadcast', 'url' => route('superadmin.announcements'), 'badge' => 'MANAGEMENT', 'keywords' => 'announcements notices alerts circulars broadcast notifications'],
                ['title' => 'Enrollment Hub', 'subtitle' => 'Master candidate enrollment workflow', 'url' => route('superadmin.enrollment'), 'badge' => 'ENROLLMENT', 'keywords' => 'enrollment hub students registrations candidate'],
                ['title' => 'Window Settings', 'subtitle' => 'Global, district & school phase dates', 'url' => route('superadmin.enrollment.window'), 'badge' => 'ENROLLMENT', 'keywords' => 'dates window schedule deadline grace period late fee open close'],
                ['title' => 'Fee Configuration', 'subtitle' => 'Rate matrix & slab definitions', 'url' => route('superadmin.enrollment.fees'), 'badge' => 'ENROLLMENT', 'keywords' => 'fees rates challans slabs amounts tariff'],
                ['title' => 'Invoice Verification', 'subtitle' => 'Bank challans desk & allotment trigger', 'url' => route('superadmin.invoices.verify'), 'badge' => 'FINANCE', 'keywords' => 'verify challan invoices approval bank payment ledger'],
                ['title' => 'Allotment History', 'subtitle' => 'Search and manual allotment registry', 'url' => route('superadmin.enrollment.allotment'), 'badge' => 'ENROLLMENT', 'keywords' => 'allotment enrollment numbers sequence manual registration'],
                ['title' => 'Special Permissions', 'subtitle' => 'Deadline extensions & fee waivers', 'url' => route('superadmin.enrollment.permissions'), 'badge' => 'ENROLLMENT', 'keywords' => 'permissions waivers extensions exemptions exceptions duplicate cnic'],
                ['title' => 'Enrollment Reports', 'subtitle' => 'Candidate census & breakdowns', 'url' => route('superadmin.reports.enrollment'), 'badge' => 'REPORTS', 'keywords' => 'reports census statistics breakdown chart graph export'],
                ['title' => 'Examination Hub', 'subtitle' => 'Master exam operations center', 'url' => route('superadmin.exam'), 'badge' => 'EXAM', 'keywords' => 'examination exam hub operations papers sessions'],
                ['title' => 'Exam Window', 'subtitle' => 'Examination phase and deadlines', 'url' => route('superadmin.exam.window'), 'badge' => 'EXAM', 'keywords' => 'exam window schedule dates announce timetable'],
                ['title' => 'Exam Fees', 'subtitle' => 'Examination fee matrix & board types', 'url' => route('superadmin.exam.fees'), 'badge' => 'EXAM', 'keywords' => 'exam fees same board other board rates tariff'],
                ['title' => 'Seat Allotment', 'subtitle' => 'Candidate roll numbers generation', 'url' => route('superadmin.exam.seats'), 'badge' => 'EXAM', 'keywords' => 'seats roll numbers allotment slips centers candidates'],
                ['title' => 'Exam Centers', 'subtitle' => 'Center capacity & school zoning', 'url' => route('superadmin.exam.centers'), 'badge' => 'EXAM', 'keywords' => 'centers buildings venues capacity allocation school assignment'],
                ['title' => 'Exam Timetable', 'subtitle' => 'Date sheets & announcements', 'url' => route('superadmin.exam.timetable'), 'badge' => 'EXAM', 'keywords' => 'timetable datesheet schedule morning evening papers subjects'],
                ['title' => 'Result Entry', 'subtitle' => 'Mark entry & statutory verification', 'url' => route('superadmin.exam.results'), 'badge' => 'EXAM', 'keywords' => 'results marks tabulation ledger gazette grading fail pass'],
                ['title' => 'Certificates Registry', 'subtitle' => 'QR verification & issuance', 'url' => route('superadmin.exam.certificates'), 'badge' => 'EXAM', 'keywords' => 'certificates diplomas pass qr verification document'],
                ['title' => 'Activity Log', 'subtitle' => 'Complete immutable board audit trail', 'url' => route('superadmin.activity-log'), 'badge' => 'AUDIT', 'keywords' => 'audit log activity history security actions trail'],
                ['title' => 'Security Dashboard', 'subtitle' => 'Failed logins & locked accounts', 'url' => route('superadmin.security'), 'badge' => 'SECURITY', 'keywords' => 'security logins locked brute force ip whitelist 2fa attempts'],
                ['title' => 'Pending Approvals', 'subtitle' => 'Queue for supervisor sign-offs', 'url' => route('superadmin.approvals'), 'badge' => 'APPROVALS', 'keywords' => 'approvals pending review signoff queue supervisor'],
                ['title' => 'System Settings', 'subtitle' => 'Board parameters & settings', 'url' => route('superadmin.settings.system'), 'badge' => 'SETTINGS', 'keywords' => 'settings configuration parameters defaults contact phone email'],
                ['title' => 'Academic Years', 'subtitle' => 'Session transition & rollover checklist', 'url' => route('superadmin.academic-years.index'), 'badge' => 'SESSION', 'keywords' => 'academic year session rollover next year transition confirm']
            ];

            $queryLower = strtolower($q);
            $navigation = array_values(array_filter($navDirectory, function ($item) use ($queryLower) {
                return str_contains(strtolower($item['title']), $queryLower)
                    || str_contains(strtolower($item['subtitle']), $queryLower)
                    || str_contains(strtolower($item['keywords']), $queryLower);
            }));

            return response()->json([
                'schools'      => $schools,
                'students'     => $students,
                'invoices'     => $invoices,
                'users'        => $users,
                'certificates' => $certificates,
                'navigation'   => array_slice($navigation, 0, 5),
            ]);

        } catch (\Throwable $e) {
            Log::error('[SearchController] Global search error: ' . $e->getMessage());
            return response()->json([
                'schools'      => [],
                'students'     => [],
                'invoices'     => [],
                'users'        => [],
                'certificates' => [],
                'navigation'   => [],
            ]);
        }
    }
}
