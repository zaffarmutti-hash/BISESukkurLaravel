import React from 'react';
import { clsx } from 'clsx';

interface AppButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  /** When set, renders as an anchor tag instead of button */
  href?: string;
  children: React.ReactNode;
}

/**
 * Polymorphic button/link component.
 * Drop-in React replacement for AppButton.vue.
 */
export function AppButton({
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled,
  href,
  type = 'button',
  className,
  children,
  ...rest
}: AppButtonProps) {
  const classes = clsx(
    'btn',
    `btn-${variant}`,
    size === 'sm' && 'btn-sm',
    size === 'lg' && 'btn-lg',
    ((disabled ?? false) || loading) && 'opacity-60 cursor-not-allowed',
    className,
  );

  const content = (
    <>
      {loading && <span className="animate-spin text-sm mr-1">↻</span>}
      {children}
    </>
  );

  if (href) {
    return (
      <a href={href} className={classes}>
        {content}
      </a>
    );
  }

  return (
    <button
      type={type}
      disabled={disabled ?? loading}
      className={classes}
      {...rest}
    >
      {content}
    </button>
  );
}
