import React, { useState, useId } from 'react';
import { useForm } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import { AppInput } from '@/components/shared/AppInput';
import { AppButton } from '@/components/shared/AppButton';

// ─── Password Strength Indicator ─────────────────────────────────────────────

interface PasswordStrengthProps {
  id?: string;
  label?: string;
  value: string;
  onChange: (val: string) => void;
  error?: string | null;
  required?: boolean;
}

function getStrength(password: string): { score: number; label: string; color: string } {
  let score = 0;
  if (password.length >= 8)  score++;
  if (/[A-Z]/.test(password)) score++;
  if (/[0-9]/.test(password)) score++;
  if (/[^A-Za-z0-9]/.test(password)) score++;

  const levels = [
    { label: 'Too short',  color: '#E5E7EB' },
    { label: 'Weak',       color: '#EF4444' },
    { label: 'Fair',       color: '#F97316' },
    { label: 'Good',       color: '#EAB308' },
    { label: 'Strong',     color: '#10B981' },
  ];
  return { score, ...levels[score] ?? levels[0]! };
}

function PasswordStrengthInput({ id: providedId, label = 'Password', value, onChange, error, required }: PasswordStrengthProps) {
  const [show, setShow] = useState(false);
  const generatedId = useId();
  const id = providedId ?? generatedId;
  const strength = getStrength(value);

  return (
    <div className="form-group">
      {label && (
        <label htmlFor={id} className={`form-label${required ? ' required' : ''}`}>{label}</label>
      )}
      <div style={{ position: 'relative' }}>
        <input
          id={id}
          type={show ? 'text' : 'password'}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          required={required}
          autoComplete="new-password"
          className={`form-input${error ? ' error' : ''}`}
          style={{ paddingRight: '2.75rem' }}
          placeholder="Minimum 8 characters"
        />
        <button
          type="button"
          onClick={() => setShow((s) => !s)}
          style={{
            position: 'absolute', right: '0.75rem', top: '50%',
            transform: 'translateY(-50%)', background: 'none',
            border: 'none', cursor: 'pointer', color: '#9CA3AF', padding: 0,
          }}
          aria-label={show ? 'Hide password' : 'Show password'}
        >
          {show ? '🙈' : '👁'}
        </button>
      </div>

      {/* Strength bar */}
      {value.length > 0 && (
        <div style={{ marginTop: '0.375rem' }}>
          <div style={{ display: 'flex', gap: '3px', marginBottom: '0.25rem' }}>
            {[0, 1, 2, 3].map((i) => (
              <div
                key={i}
                style={{
                  flex: 1, height: '3px', borderRadius: '9999px',
                  background: i < strength.score ? strength.color : '#E5E7EB',
                  transition: 'background 0.3s ease',
                }}
              />
            ))}
          </div>
          <p style={{ fontSize: '0.75rem', color: strength.color, fontWeight: 600 }}>
            {strength.label}
          </p>
        </div>
      )}

      {error && <p className="form-error">{error}</p>}
    </div>
  );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

/**
 * Change Password page — forced password change on first login.
 * File: resources/js/pages/auth/ChangePassword.tsx
 */
export default function ChangePassword() {
  const form = useForm({
    password: '',
    password_confirmation: '',
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    form.post(route('password.change.store'));
  }

  return (
    <AuthLayout
      heading="Change Password"
      subheading="You must set a new password before continuing."
    >
      <form onSubmit={handleSubmit}>
        <PasswordStrengthInput
          id="password"
          label="New Password"
          value={form.data.password}
          onChange={(val) => form.setData('password', val)}
          error={form.errors.password}
          required
        />

        <AppInput
          id="password_confirmation"
          label="Confirm Password"
          type="password"
          value={form.data.password_confirmation}
          onChange={(e) => form.setData('password_confirmation', e.target.value)}
          error={form.errors.password_confirmation}
          required
          autoComplete="new-password"
          className="mt-4"
        />

        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          loading={form.processing}
          className="w-full justify-center mt-6"
        >
          Update Password
        </AppButton>
      </form>
    </AuthLayout>
  );
}
