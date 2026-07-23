<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Certificate;
use App\Modules\Admin\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request): Response
    {
        $year = AcademicYear::current();
        $yearId = $year?->id;

        $query = Certificate::with(['student.school', 'academicYear']);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function($sq) use ($search) {
                $sq->where('full_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_number', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => Certificate::count(),
            'ssc'   => Certificate::where('level', 'ssc')->count(),
            'hsc'   => Certificate::where('level', 'hsc')->count(),
        ];

        return Inertia::render('admin/examination/Certificates', [
            'certificates' => $query->latest()->paginate(15)->withQueryString(),
            'stats'        => $stats,
            'activeYear'   => $year,
            'filters'      => $request->only(['level', 'search']),
        ]);
    }

    public function generate(Request $request)
    {
        $year = AcademicYear::current();
        if (!$year) {
            return back()->with('error', 'No active academic year configured.');
        }

        $validated = $request->validate([
            'level' => 'required|in:ssc,hsc',
        ]);

        $count = $this->certificateService->generateForYear($year->id, $validated['level']);

        if ($count === 0) {
            return back()->with('warning', 'No new eligible candidates found who passed all exams in ' . strtoupper($validated['level']) . '.');
        }

        activity('certificate')
            ->causedBy(Auth::user())
            ->withProperties([
                'level' => $validated['level'],
                'count' => $count,
                'year'  => $year->label,
            ])
            ->log("Generated {$count} certificates for {$validated['level']}");

        return back()->with('success', "Certificate generation completed successfully. Issued {$count} new certificates.");
    }
}
