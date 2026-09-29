<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class SecurityController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            // ── Summary Cards ──────────────────────────────────────────────
            $failedLoginsToday = Activity::where('log_name', 'auth')
                ->where('description', 'LIKE', '%failed%')
                ->whereDate('created_at', today())
                ->count();

            $lockedAccounts = User::where('locked_until', '>', now())->count();

            // Password expiry: users whose password was last changed > 60 days ago
            // (Using last_login_at as a proxy when no password_changed_at column exists)
            $expiringPasswords = User::where('is_active', true)
                ->whereNull('school_id') // Board users only
                ->where(function ($q) {
                    $q->whereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(60));
                })
                ->count();

            $inactiveBoardUsers = User::where('is_active', true)
                ->whereNull('school_id')
                ->where(function ($q) {
                    $q->whereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(30));
                })
                ->count();

            // ── Failed Login Table ─────────────────────────────────────────
            $failedLogins = Activity::where('log_name', 'auth')
                ->where('description', 'LIKE', '%failed%')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($a) => [
                    'id'          => $a->id,
                    'description' => $a->description,
                    'user'        => $a->causer?->name ?? ($a->properties['username'] ?? 'Unknown'),
                    'ip'          => $a->properties['ip'] ?? '—',
                    'created_at'  => $a->created_at,
                ]);

            // ── Locked Accounts ────────────────────────────────────────────
            $lockedUsers = User::where('locked_until', '>', now())
                ->with('school:id,name')
                ->get(['id', 'name', 'username', 'role', 'school_id', 'locked_until', 'failed_login_attempts']);

            // ── Recent Auth Activity ───────────────────────────────────────
            $recentAuthActivity = Activity::where('log_name', 'auth')
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn ($a) => [
                    'id'          => $a->id,
                    'description' => $a->description,
                    'user'        => $a->causer?->name ?? 'Unknown',
                    'ip'          => $a->properties['ip'] ?? '—',
                    'success'     => !str_contains(strtolower($a->description), 'fail'),
                    'created_at'  => $a->created_at,
                ]);

            // ── Inactive Board Users ───────────────────────────────────────
            $inactiveBoardUsersList = User::where('is_active', true)
                ->whereNull('school_id')
                ->where(function ($q) {
                    $q->whereNull('last_login_at')
                        ->orWhere('last_login_at', '<', now()->subDays(30));
                })
                ->orderBy('last_login_at')
                ->limit(15)
                ->get(['id', 'name', 'username', 'role', 'last_login_at']);

        } catch (\Throwable $e) {
            Log::error('[SecurityController] Failed to load security data: ' . $e->getMessage());
            $failedLoginsToday = 0;
            $lockedAccounts = 0;
            $expiringPasswords = 0;
            $inactiveBoardUsers = 0;
            $failedLogins = collect();
            $lockedUsers = collect();
            $recentAuthActivity = collect();
            $inactiveBoardUsersList = collect();
        }

        return view('superadmin.security.index', compact(
            'failedLoginsToday',
            'lockedAccounts',
            'expiringPasswords',
            'inactiveBoardUsers',
            'failedLogins',
            'lockedUsers',
            'recentAuthActivity',
            'inactiveBoardUsersList'
        ));
    }

    /**
     * Unlock a user account.
     */
    public function unlockUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $user->update([
            'locked_until'          => null,
            'failed_login_attempts' => 0,
        ]);

        activity('security')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Account manually unlocked by Super Admin');

        return back()->with('success', "Account for {$user->name} has been unlocked.");
    }
}
