import React from 'react';
import { useForm } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import { AppInput } from '@/components/shared/AppInput';
import { AppButton } from '@/components/shared/AppButton';
import { PasswordInput } from '@/components/shared/PasswordInput';

export default function Login() {
  const form = useForm({
    username: '',
    password: '',
    remember: false as boolean,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('login.store'), {
      onFinish: () => form.reset('password'),
    });
  }

  return (
    <AuthLayout>
      <form onSubmit={handleSubmit} className="auth-login-form">
        <AppInput
          id="username"
          label="Username"
          type="text"
          placeholder="Enter your username"
          value={form.data.username}
          onChange={(e) => form.setData('username', e.target.value)}
          error={form.errors.username}
          required
          autoComplete="username"
        />

        <PasswordInput
          id="password"
          label="Password"
          placeholder="Enter your password"
          value={form.data.password}
          onChange={(e) => form.setData('password', e.target.value)}
          error={form.errors.password}
          required
          autoComplete="current-password"
        />

        <div className="auth-remember-row">
          <label className="auth-remember-label">
            <input
              id="remember"
              type="checkbox"
              checked={form.data.remember}
              onChange={(e) => form.setData('remember', e.target.checked)}
              className="auth-checkbox"
            />
            <span>Remember me</span>
          </label>
        </div>

        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          loading={form.processing}
          className="auth-submit-btn w-full justify-center"
        >
          Sign In
        </AppButton>
      </form>
    </AuthLayout>
  );
}
