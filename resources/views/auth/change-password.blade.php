@extends('layouts.auth')

@section('content')
    <form method="POST" action="{{ route('password.change.store') }}" class="auth-login-form">
        @csrf

        <div class="space-y-5">
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-900">New Password</label>
                <div class="relative mt-2">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-11 text-sm text-gray-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                        placeholder="Enter a new password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        onclick="togglePasswordVisibility('password', 'eyeIcon1', 'eyeSlashIcon1')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-700 transition focus:outline-none"
                        aria-label="Toggle password visibility"
                        title="Show/Hide password"
                    >
                        <svg id="eyeIcon1" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeSlashIcon1" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-900">Confirm Password</label>
                <div class="relative mt-2">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-11 text-sm text-gray-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                        placeholder="Confirm your new password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        onclick="togglePasswordVisibility('password_confirmation', 'eyeIcon2', 'eyeSlashIcon2')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-700 transition focus:outline-none"
                        aria-label="Toggle password confirmation visibility"
                        title="Show/Hide password"
                    >
                        <svg id="eyeIcon2" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeSlashIcon2" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="auth-submit-btn w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300"
            >
                Update Password
            </button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, eyeId, eyeSlashId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeSlash = document.getElementById(eyeSlashId);

            if (!input || !eye || !eyeSlash) return;

            if (input.type === 'password') {
                input.type = 'text';
                eye.style.display = 'none';
                eyeSlash.style.display = 'block';
            } else {
                input.type = 'password';
                eye.style.display = 'block';
                eyeSlash.style.display = 'none';
            }
        }
    </script>
@endsection
