<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\School;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** Board-level roles only. School-level roles are created per-school. */
    private function boardRoles(): array
    {
        return [
            ['value' => 'super_admin',              'label' => 'Super Admin'],
            ['value' => 'controller',               'label' => 'Controller of Examinations'],
            ['value' => 'assistant_controller',     'label' => 'Assistant Controller'],
            ['value' => 'fee_manager',              'label' => 'Fee Manager'],
            ['value' => 'accounts_officer',         'label' => 'Accounts Officer'],
            ['value' => 'check_and_balance_officer','label' => 'Check & Balance Officer'],
            ['value' => 'data_entry_operator',      'label' => 'Data Entry Operator'],
            ['value' => 'result_entry_operator',    'label' => 'Result Entry Operator'],
            ['value' => 'marksheet_printer',        'label' => 'Marksheet Printer'],
            ['value' => 'announcement_officer',     'label' => 'Announcement Officer'],
            ['value' => 'district_admin',           'label' => 'District Admin'],
        ];
    }

    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $query = User::with(['school:id,name,username', 'district:id,name'])->latest();

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
            'roles'     => $this->boardRoles(),
        ]);
    }

    public function create(): \Illuminate\Contracts\View\View
    {
        return view('superadmin.users.create', [
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'username']),
            'roles'     => $this->boardRoles(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'username'    => ['required', 'string', 'max:100', 'unique:users,username'],
            'name'        => ['required', 'string', 'max:150'],
            'password'    => ['required', 'confirmed', new StrongPassword],
            'email'       => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'role'        => ['required', 'string'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'is_active'   => ['boolean'],
        ]);

        $user = User::create([
            'username'    => $data['username'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'district_id' => $data['role'] === 'district_admin' ? ($data['district_id'] ?? null) : null,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        $user->syncRoles([$data['role']]);

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('Board user created');

        return redirect()->route('superadmin.users.index')
            ->with('success', "User '{$user->username}' created successfully.");
    }

    public function show(User $user): \Illuminate\Contracts\View\View
    {
        $user->load(['school.district', 'district']);

        return view('superadmin.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): \Illuminate\Contracts\View\View
    {
        return view('superadmin.users.edit', [
            'user'      => $user->load(['school:id,name,username', 'district:id,name']),
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'schools'   => School::orderBy('name')->get(['id', 'name', 'username']),
            'roles'     => $this->boardRoles(),
        ]);
    }

    public function update(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'username'    => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'name'        => ['required', 'string', 'max:150'],
            'email'       => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role'        => ['required', 'string'],
            'district_id' => ['nullable', 'exists:districts,id'],
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
            'is_active'   => $data['is_active'] ?? $user->is_active,
        ];

        if ($request->boolean('change_password')) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        $user->syncRoles([$data['role']]);

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('Board user updated');

        return redirect()->route('superadmin.users.index')
            ->with('success', "User '{$user->username}' updated successfully.");
    }

    public function resetPassword(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $user->update([
            'password'             => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('Password reset by Super Admin');

        return back()->with('success', 'Password reset successfully. User must change it on next login.');
    }

    public function toggleActive(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        activity('user')->causedBy(Auth::user())->performedOn($user)
            ->log($user->is_active ? 'User activated' : 'User deactivated');

        return back()->with('success', 'User status updated.');
    }

    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        activity('user')->causedBy(Auth::user())->performedOn($user)->log('User deleted');
        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully.');
    }
}
