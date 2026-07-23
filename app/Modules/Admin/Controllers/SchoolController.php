<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SchoolController extends Controller
{
    public function index(Request $request): Response
    {
        $query = School::with('district', 'adminUser');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        return Inertia::render('admin/schools/Index', [
            'schools'   => $query->latest()->paginate(20)->withQueryString(),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['district_id', 'type', 'is_active', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/schools/Create', [
            'districts' => District::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:schools,username',
            'type' => 'required|in:school,college',
            'gender' => 'required|in:male,female,mixed',
            'principal_name' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'admin_email' => 'required|email|unique:users,email',
            'admin_name' => 'required|string|max:150',
            'admin_password' => 'required|string|min:8',
        ]);

        $school = School::create([
            'district_id' => $validated['district_id'],
            'name' => $validated['name'],
            'username' => $validated['code'],
            'type' => $validated['type'],
            'gender' => $validated['gender'],
            'principal_name' => $validated['principal_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_active' => true,
        ]);

        User::create([
            'school_id' => $school->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'school_admin',
            'is_active' => true,
        ])->assignRole('school_admin');

        activity('school')->causedBy(Auth::user())->performedOn($school)->log('School registered with admin account');

        $redirectRoute = $request->routeIs('superadmin.*')
            ? 'superadmin.schools'
            : 'admin.schools.index';

        return redirect()->route($redirectRoute)->with('success', 'School created successfully.');
    }

    public function show(School $school): Response
    {
        return Inertia::render('admin/schools/Show', [
            'school' => $school->load('district', 'adminUser', 'students'),
        ]);
    }

    public function edit(School $school): Response
    {
        return Inertia::render('admin/schools/Edit', [
            'school' => $school,
            'districts' => District::all(),
        ]);
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:schools,username,' . $school->id,
            'type' => 'required|in:school,college',
            'gender' => 'required|in:male,female,mixed',
            'principal_name' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'is_active' => 'required|boolean',
        ]);

        $wasActive = $school->is_active;
        
        $data = $validated;
        $data['username'] = $validated['code'];
        unset($data['code']);
        $school->update($data);

        if ($wasActive !== (bool) $validated['is_active']) {
            activity('school')->causedBy(Auth::user())->performedOn($school)
                ->log($validated['is_active'] ? 'School activated' : 'School suspended');
        } else {
            activity('school')->causedBy(Auth::user())->performedOn($school)->log('School configuration updated');
        }

        return back()->with('success', 'School updated successfully.');
    }
}
