<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Certificate;
use App\Modules\Admin\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request)
    {
        return redirect()->route('superadmin.exam.certificates');
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
