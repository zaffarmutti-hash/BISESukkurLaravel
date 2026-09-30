<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\School;
use App\Models\SchoolException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialPermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolException::with([
            'school:id,name,username',
            'grantedBy:id,name',
            'revokedBy:id,name',
            'academicYear:id,label',
        ])->latest();

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true)
                ->whereNull('revoked_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
        }

        if ($request->filled('exception_type')) {
            $query->where('exception_type', $request->input('exception_type'));
        }

        return view('superadmin.special_permissions', [
            'exceptions' => $query->paginate(25)->withQueryString(),
            'filters'    => $request->only(['active_only', 'exception_type']),
            'schools'    => School::orderBy('name')->get(['id', 'name', 'username']),
            'districts'  => District::orderBy('name')->get(['id', 'name']),
            'years'      => AcademicYear::orderByDesc('year_start')->get(['id', 'label']),
            'types'      => [
                ['value' => 'allow_record_edit_after_lock', 'label' => 'Allow Record Edit After Lock'],
                ['value' => 'extend_enrollment_deadline', 'label' => 'Extend Enrollment Deadline'],
                ['value' => 'extend_examination_deadline', 'label' => 'Extend Examination Deadline'],
                ['value' => 'allow_challan_resubmission', 'label' => 'Allow Challan Resubmission'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id'        => ['required', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'exception_type'   => ['required', 'in:allow_record_edit_after_lock,extend_enrollment_deadline,extend_examination_deadline,allow_challan_resubmission,other'],
            'reason'           => ['required', 'string', 'min:10', 'max:1000'],
            'expires_at'       => ['nullable', 'date', 'after:today'],
            'student_id'       => ['nullable', 'exists:students,id'],
        ]);

        $exception = SchoolException::create([
            ...$data,
            'granted_by' => Auth::id(),
            'is_active'  => true,
        ]);

        activity('exception')
            ->causedBy(Auth::user())
            ->performedOn($exception)
            ->withProperties(['type' => $data['exception_type'], 'school_id' => $data['school_id']])
            ->log('Special permission granted');

        return back()->with('success', 'Exception granted successfully.');
    }

    public function revoke(SchoolException $exception)
    {
        if ($exception->revoked_at) {
            return back()->with('warning', 'This exception has already been revoked.');
        }

        $exception->update([
            'is_active'  => false,
            'revoked_at' => now(),
            'revoked_by' => Auth::id(),
        ]);

        activity('exception')
            ->causedBy(Auth::user())
            ->performedOn($exception)
            ->log('Special permission revoked');

        return back()->with('success', 'Exception revoked.');
    }
}
