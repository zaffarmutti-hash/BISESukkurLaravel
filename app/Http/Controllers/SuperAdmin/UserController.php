<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\School;
use App\Models\User;
use App\Notifications\PasswordResetByAdmin;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['school:id,name,username', 'district:id,name'])
            ->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return view('superadmin.users.index', [
            'users'     => $query->paginate(20)->withQueryString(),
            'filters'   => $request->only(['role', 'district_id', 'school_id', 'is_active', 'search']),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'username']),
            'roles'     => [
                ['value' => 'super_admin', 'label' => 'Super Admin'],
                ['value' => 'district_admin', 'label' => 'District Admin'],
                ['value' => 'school_admin', 'label' => 'School Admin'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('superadmin/users/Create', [
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'username']),
            'roles'     => [
                ['value' => 'super_admin', 'label' => 'Super Admin'],
                ['value' => 'district_admin', 'label' => 'District Admin'],
                ['value' => 'school_admin', 'label' => 'School Admin'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username'              => ['required', 'string', 'max:100', 'unique:users,username'],
            'name'                  => ['required', 'string', 'max:150'],
            'password'              => ['required', 'confirmed', new StrongPassword],
            'email'                 => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'role'                  => ['required', 'in:super_admin,district_admin,school_admin'],
            'district_id'           => ['nullable', 'required_if:role,district_admin', 'exists:districts,id'],
            'school_id'             => ['nullable', 'required_if:role,school_admin', 'exists:schools,id'],
            'is_active'             => ['boolean'],
        ]);

        $user = User::create([
            'username'    => $data['username'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'district_id' => $data['role'] === 'district_admin' ? $data['district_id'] : null,
            'school_id'   => $data['role'] === 'school_admin' ? $data['school_id'] : null,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        $user->syncRoles([$data['role']]);

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('User created');

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('superadmin/users/Edit', [
            'user'      => $user->load(['school:id,name,username', 'district:id,name']),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'username']),
            'roles'     => [
                ['value' => 'super_admin', 'label' => 'Super Admin'],
                ['value' => 'district_admin', 'label' => 'District Admin'],
                ['value' => 'school_admin', 'label' => 'School Admin'],
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'username'    => ['required', 'string', 'max:100', 'unique:users,username,'.$user->id],
            'name'        => ['required', 'string', 'max:150'],
            'email'       => ['nullable', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'role'        => ['required', 'in:super_admin,district_admin,school_admin'],
            'district_id' => ['nullable', 'required_if:role,district_admin', 'exists:districts,id'],
            'school_id'   => ['nullable', 'required_if:role,school_admin', 'exists:schools,id'],
            'is_active'   => ['boolean'],
        ];

        if ($request->boolean('change_password')) {
            $rules['password'] = ['required', 'confirmed', new StrongPassword];
        }

        $data = $request->validate($rules);

        $update = [
            'username'    => $data['username'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'role'        => $data['role'],
            'district_id' => $data['role'] === 'district_admin' ? ($data['district_id'] ?? null) : null,
            'school_id'   => $data['role'] === 'school_admin' ? ($data['school_id'] ?? null) : null,
            'is_active'   => $data['is_active'] ?? $user->is_active,
        ];

        if ($request->boolean('change_password')) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        $user->syncRoles([$data['role']]);

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('User updated');

        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $user->update([
            'password'             => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        if ($user->email) {
            $user->notify(new PasswordResetByAdmin(Auth::user()));
        }

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('Password reset by Super Admin');

        return back()->with('success', 'Password reset successfully.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        activity('user')->causedBy(Auth::user())->performedOn($user)
            ->log($user->is_active ? 'User activated' : 'User deactivated');

        return back()->with('success', 'User status updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('User deleted');

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully.');
    }
}
