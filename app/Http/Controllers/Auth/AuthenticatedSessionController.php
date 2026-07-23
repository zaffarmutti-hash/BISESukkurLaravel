<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function create(): Response
    {
        return Inertia::render('auth/Login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->authService->attemptLogin(
            $request,
            $request->input('username'),
            $request->input('password'),
            $request->boolean('remember')
        );

        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(route($user->getDashboardRoute()));
    }

    public function destroy(Request $request)
    {
        $this->authService->logout($request);

        return redirect()->route('login');
    }
}
