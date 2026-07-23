import { Link } from '@inertiajs/react';
import { clsx } from 'clsx';

type Gradient =
  | 'purple' | 'pink' | 'hot' | 'cyan' | 'green'
  | 'orange' | 'red' | 'mint' | 'soft' | 'healthy' | 'unhealthy';

interface GradientStatCardProps {
  label: string;
  value?: number | string | null;
  href?: string;
  gradient?: Gradient;
  loading?: boolean;
}

function formatValue(v: number | string | null | undefined): string {
  if (v === null || v === undefined) return '—';
  if (typeof v === 'number') {
    return Number.isInteger(v)
      ? v.toLocaleString()
      : v.toLocaleString(undefined, { minimumFractionDigits: 2 });
  }
  return v;
}

/**
 * Gradient statistics card used in the SuperAdmin Dashboard.
 * Drop-in React replacement for GradientStatCard.vue.
 */
export function GradientStatCard({
  label,
  value,
  href,
  gradient = 'purple',
  loading = false,
}: GradientStatCardProps) {
  const content = loading ? (
    <div className="animate-pulse">
      <div className="h-8 bg-white/20 rounded w-16 mb-2" />
      <div className="h-4 bg-white/10 rounded w-24" />
    </div>
  ) : (
    <>
      <div className="gradient-stat-value">{formatValue(value)}</div>
      <div className="gradient-stat-label">{label}</div>
    </>
  );

  const classes = clsx('gradient-stat-card', `gradient-${gradient}`, href && 'cursor-pointer');

  if (href) {
    return (
      <Link href={href} className={classes}>
        {content}
      </Link>
    );
  }

  return <div className={classes}>{content}</div>;
}
