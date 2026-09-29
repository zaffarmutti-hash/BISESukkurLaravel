import { Link } from '@inertiajs/react';
import { clsx } from 'clsx';

type Gradient =
  | 'purple' | 'pink' | 'hot' | 'cyan' | 'green'
  | 'orange' | 'red' | 'mint' | 'soft' | 'healthy' | 'unhealthy';

interface GradientStatCardProps {
  label: string;
  value?: number | string | null;
  subtitle?: string;
  href?: string;
  gradient?: Gradient;
  loading?: boolean;
  icon?: React.ReactNode;
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
 * Enhanced Gradient statistics card used in SuperAdmin and Admin Dashboards.
 */
export function GradientStatCard({
  label,
  value,
  subtitle,
  href,
  gradient = 'purple',
  loading = false,
  icon,
}: GradientStatCardProps) {
  const content = loading ? (
    <div className="animate-pulse flex flex-col justify-between h-full">
      <div>
        <div className="h-4 bg-white/20 rounded w-24 mb-3" />
        <div className="h-8 bg-white/30 rounded w-16 mb-2" />
      </div>
      <div className="h-3 bg-white/20 rounded w-20" />
    </div>
  ) : (
    <div className="relative z-10 flex flex-col justify-between h-full min-h-[100px]">
      <div className="flex items-start justify-between gap-2">
        <span className="gradient-stat-label">{label}</span>
        {icon && <div className="w-8 h-8 rounded-lg bg-white/15 backdrop-blur-md flex items-center justify-center flex-shrink-0 text-white shadow-inner">{icon}</div>}
      </div>
      <div>
        <div className="gradient-stat-value tracking-tight font-extrabold">{formatValue(value)}</div>
        {subtitle && <div className="text-xs text-white/80 font-medium mt-1">{subtitle}</div>}
      </div>
      <div className="gradient-stat-circle" />
    </div>
  );

  const classes = clsx('gradient-stat-card relative overflow-hidden transition-all duration-300', `gradient-${gradient}`, href && 'cursor-pointer hover:-translate-y-1 hover:shadow-xl');

  if (href) {
    return (
      <Link href={href} className={classes}>
        {content}
      </Link>
    );
  }

  return <div className={classes}>{content}</div>;
}

