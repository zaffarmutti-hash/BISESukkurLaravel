import React, { useId } from 'react';
import { clsx } from 'clsx';

interface AppInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string | null;
  hint?: string | null;
  /** Set to 'cnic' to render as text with monospace styling */
  type?: string;
}

/**
 * Controlled text input with label, hint, and inline error display.
 * Drop-in React replacement for AppInput.vue.
 */
export function AppInput({
  label,
  error,
  hint,
  type = 'text',
  required,
  className,
  id: providedId,
  ...rest
}: AppInputProps) {
  const generatedId = useId();
  const id = providedId ?? generatedId;
  const inputType = type === 'cnic' ? 'text' : type;

  return (
    <div className="form-group">
      {label && (
        <label
          htmlFor={id}
          className={clsx('form-label', required && 'required')}
        >
          {label}
        </label>
      )}
      <input
        id={id}
        type={inputType}
        required={required}
        className={clsx(
          'form-input',
          error && 'error',
          type === 'cnic' && 'cnic',
          className,
        )}
        {...rest}
      />
      {hint && !error && <p className="form-hint">{hint}</p>}
      {error && <p className="form-error">{error}</p>}
    </div>
  );
}
