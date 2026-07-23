import React, { useId, useState } from 'react';
import { clsx } from 'clsx';

interface PasswordInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string | null;
  hint?: string | null;
}

export function PasswordInput({
  label,
  error,
  hint,
  required,
  className,
  id: providedId,
  ...rest
}: PasswordInputProps) {
  const [showPassword, setShowPassword] = useState(false);
  const generatedId = useId();
  const id = providedId ?? generatedId;

  return (
    <div className="form-group relative">
      {label && (
        <label htmlFor={id} className={clsx('form-label', required && 'required')}>
          {label}
        </label>
      )}

      <input
        id={id}
        type={showPassword ? 'text' : 'password'}
        required={required}
        className={clsx('form-input pr-12', error && 'error', className)}
        {...rest}
      />

      <button
        type="button"
        className="password-toggle absolute inset-y-0 right-3 flex items-center justify-center rounded-md text-slate-500 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400"
        onClick={() => setShowPassword((current) => !current)}
        aria-label={showPassword ? 'Hide password' : 'Show password'}
      >
        {showPassword ? (
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19.5c-5.33 0-9.8-3.68-11.28-8.5a10.94 10.94 0 0 1 1.64-3.03" />
            <path d="M1 1l22 22" />
            <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12" />
          </svg>
        ) : (
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle cx="12" cy="12" r="3" />
          </svg>
        )}
      </button>

      {hint && !error && <p className="form-hint">{hint}</p>}
      {error && <p className="form-error">{error}</p>}
    </div>
  );
}
