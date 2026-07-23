<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PasswordChangeController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/ChangePassword');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password'              => ['required', 'confirmed', new StrongPassword],
            'password_confirmation' => ['required'],
        ]);

        $user = Auth::user();
        $user->update([
            'password'             => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        return redirect()
            ->route($user->getDashboardRoute())
            ->with('success', 'Password updated successfully.');
    }
}
