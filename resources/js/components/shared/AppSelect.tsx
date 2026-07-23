import React, { useId } from 'react';
import { clsx } from 'clsx';
import type { SelectOption } from '@/types';

interface AppSelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string | null;
  placeholder?: string;
  /** Array of { value, label } objects or plain strings */
  options?: (SelectOption | string)[];
}

/**
 * Controlled select input with label and error display.
 * Drop-in React replacement for AppSelect.vue.
 */
export function AppSelect({
  label,
  error,
  placeholder = 'Select…',
  options = [],
  required,
  className,
  id: providedId,
  value,
  onChange,
  ...rest
}: AppSelectProps) {
  const generatedId = useId();
  const id = providedId ?? generatedId;

  const normalizedOptions: SelectOption[] = options.map((o) =>
    typeof o === 'string' ? { value: o, label: o } : o,
  );

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
      <select
        id={id}
        value={value}
        onChange={onChange}
        required={required}
        className={clsx('form-select', error && 'error', className)}
        {...rest}
      >
        {placeholder && (
          <option value="" disabled>
            {placeholder}
          </option>
        )}
        {normalizedOptions.map((opt) => (
          <option key={String(opt.value)} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
      {error && <p className="form-error">{error}</p>}
    </div>
  );
}
