import { clsx } from 'clsx';

type Variant = 'success' | 'warning' | 'danger' | 'neutral' | 'info';

interface StatusBadgeProps {
  label: string;
  variant?: Variant;
  pulse?: boolean;
  className?: string;
}

const variantClass: Record<Variant, string> = {
  success: 'badge-success',
  warning: 'badge-warning',
  danger: 'badge-danger',
  neutral: 'badge-neutral',
  info: 'badge-info',
};

export function StatusBadge({ label, variant = 'neutral', pulse = false, className }: StatusBadgeProps) {
  return (
    <span className={clsx('badge', variantClass[variant], pulse && 'badge-pulse', className)}>
      {label}
    </span>
  );
}
