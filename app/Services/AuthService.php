<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function attemptLogin(Request $request, string $username, string $password, bool $remember = false): User
    {
        $user = User::where('username', $username)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => 'These credentials do not match our records.',
            ]);
        }

        if ($user->isLocked()) {
            throw ValidationException::withMessages([
                'username' => 'Account locked until '.$user->locked_until->format('M d, Y h:i A').'.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Your account has been deactivated. Contact the board office.',
            ]);
        }

        if (! Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
            $user->recordFailedLogin();

            if ($user->fresh()->isLocked()) {
                throw ValidationException::withMessages([
                    'username' => 'Too many failed attempts. Account locked until '
                        .$user->fresh()->locked_until->format('M d, Y h:i A').'.',
                ]);
            }

            throw ValidationException::withMessages([
                'username' => 'These credentials do not match our records.',
            ]);
        }

        /** @var User $authenticated */
        $authenticated = Auth::user();
        $authenticated->recordSuccessfulLogin();

        activity('auth')
            ->causedBy($authenticated)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('User logged in');

        return $authenticated;
    }

    public function logout(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            activity('auth')
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
