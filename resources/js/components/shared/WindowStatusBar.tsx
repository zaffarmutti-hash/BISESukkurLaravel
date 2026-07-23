import { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/shared/StatusBadge';
import type { WindowPhaseResult } from '@/types';

interface WindowStatusBarProps {
  activeYearLabel?: string | null;
  enrollmentOpen?: boolean;
  examinationOpen?: boolean;
  enrollmentPhase?: WindowPhaseResult | null;
  examinationPhase?: WindowPhaseResult | null;
  enrollmentHref?: string;
  examinationHref?: string;
}

export function WindowStatusBar({
  activeYearLabel,
  enrollmentOpen = false,
  examinationOpen = false,
  enrollmentPhase,
  examinationPhase,
  enrollmentHref,
  examinationHref,
}: WindowStatusBarProps) {
  
  // Local state for tickers if countdowns are present
  const [now, setNow] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setNow(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  function formatTimeRemaining(targetStr: string | null | undefined): string {
    if (!targetStr) return '';
    const target = new Date(targetStr);
    const diff = target.getTime() - now.getTime();
    if (diff <= 0) return 'Transitioning...';

    const secs = Math.floor(diff / 1000);
    const mins = Math.floor(secs / 60);
    const hours = Math.floor(mins / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) {
      return `${days}d ${hours % 24}h remaining`;
    }
    if (hours > 0) {
      return `${hours}h ${mins % 60}m remaining`;
    }
    if (mins > 0) {
      return `${mins}m ${secs % 60}s remaining`;
    }
    return `${secs}s remaining`;
  }

  function renderPhaseIndicator(
    label: string,
    legacyOpen: boolean,
    phaseResult: WindowPhaseResult | null | undefined,
    href?: string
  ) {
    let phase: 'normal' | 'grace' | 'closed' = legacyOpen ? 'normal' : 'closed';
    let labelText = legacyOpen ? 'Open' : 'Closed';
    let badgeVariant: 'success' | 'warning' | 'neutral' | 'danger' = legacyOpen ? 'success' : 'neutral';
    let subtext = '';

    if (phaseResult) {
      phase = phaseResult.phase;
      if (phase === 'normal') {
        labelText = 'Normal Phase';
        badgeVariant = 'success';
        if (phaseResult.next_transition) {
          subtext = `Grace period starts: ${formatTimeRemaining(phaseResult.next_transition)}`;
        }
      } else if (phase === 'grace') {
        labelText = phaseResult.is_exception_based ? 'Special Extension' : 'Grace Period (Late Fee)';
        badgeVariant = 'warning';
        if (phaseResult.next_transition) {
          subtext = `Closes: ${formatTimeRemaining(phaseResult.next_transition)}`;
        } else if (phaseResult.is_exception_based) {
          subtext = 'Individual school extension';
        }
      } else {
        labelText = 'Closed';
        badgeVariant = 'neutral';
        subtext = phaseResult.next_transition_label || 'Closed';
      }
    }

    const badge = (
      <div className="flex flex-col items-start sm:items-end gap-0.5">
        <div className="flex items-center gap-2">
          <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">{label}</span>
          <StatusBadge label={labelText} variant={badgeVariant} />
        </div>
        {subtext && <span className="text-[10px] text-gray-400 font-medium">{subtext}</span>}
      </div>
    );

    if (href) {
      return (
        <Link href={href} className="hover:opacity-90 transition-opacity">
          {badge}
        </Link>
      );
    }

    return badge;
  }

  return (
    <div className="flex flex-col lg:flex-row lg:items-center gap-4 p-4 rounded-xl border border-slate-200 bg-white shadow-sm mb-6">
      <div className="flex items-center gap-2">
        <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Active Year</span>
        <span className="text-lg font-extrabold text-slate-800 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">
          {activeYearLabel ?? 'None set'}
        </span>
      </div>
      <div className="flex flex-wrap items-center gap-6 lg:ml-auto">
        {renderPhaseIndicator('Enrollment', enrollmentOpen, enrollmentPhase, enrollmentHref)}
        <div className="hidden sm:block h-8 w-[1px] bg-slate-100" />
        {renderPhaseIndicator('Examination', examinationOpen, examinationPhase, examinationHref)}
      </div>
    </div>
  );
}
