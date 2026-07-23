import React from 'react';
import { clsx } from 'clsx';

type AlertType = 'info' | 'success' | 'warning' | 'error';

interface AppAlertProps {
  type?: AlertType;
  message: string;
  className?: string;
}

const styles: Record<AlertType, string> = {
  info:    'alert-info',
  success: 'alert-success',
  warning: 'alert-warning',
  error:   'alert-error',
};

const icons: Record<AlertType, React.ReactNode> = {
  info: (
    <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
  ),
  success: (
    <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <polyline points="20,6 9,17 4,12"/>
    </svg>
  ),
  warning: (
    <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
  ),
  error: (
    <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
    </svg>
  ),
};

/**
 * Alert banner for flash messages and inline notifications.
 * Drop-in React replacement for AppAlert.vue.
 */
export function AppAlert({ type = 'info', message, className }: AppAlertProps) {
  return (
    <div className={clsx('alert', styles[type], className)}>
      {icons[type]}
      <span>{message}</span>
    </div>
  );
}
