import { Link, usePage } from '@inertiajs/react';
import { clsx } from 'clsx';

// Simple emoji/unicode icon map — matches the original NavItem.vue
const iconMap: Record<string, string> = {
  'grid':               '⊞',
  'calendar':           '📅',
  'currency':           '₨',
  'check-circle':       '✓',
  'chart-bar':          '📊',
  'building':           '🏛',
  'clock':              '⏱',
  'user-group':         '👥',
  'pencil':             '✏',
  'badge-check':        '🎓',
  'library':            '🏫',
  'shield-exclamation': '⚠',
  'document-text':      '📄',
  'home':               '🏠',
  'users':              '👥',
  'plus-circle':        '+',
  'receipt-tax':        '📋',
  'document-add':       '📝',
  'clipboard-list':     '📋',
  'heart':              '♥',
  'dot':                '•',
  'clipboard':          '📋',
  'shield':             '🛡',
  'academic-cap':       '🎓',
  'refresh':            '↻',
};

interface NavItemProps {
  href?: string;
  icon?: string;
  label: string;
  badge?: number;
  className?: string;
  onClick?: () => void;
}

/**
 * Sidebar navigation item.
 * Drop-in React replacement for NavItem.vue.
 * Renders as Inertia Link when href is provided, button otherwise.
 */
export function NavItem({
  href,
  icon = 'dot',
  label,
  badge,
  className,
  onClick,
}: NavItemProps) {
  const { url } = usePage();

  const isActive = href
    ? (() => {
        try {
          const pathname = new URL(href, window.location.origin).pathname;
          return url.startsWith(pathname);
        } catch {
          return false;
        }
      })()
    : false;

  const classes = clsx('app-nav-item', isActive && 'active', className);

  const inner = (
    <>
      <span className="nav-icon">{iconMap[icon] ?? '•'}</span>
      <span>{label}</span>
      {badge !== undefined && badge > 0 && (
        <span className="ml-auto text-xs bg-amber-500 text-white font-bold px-1.5 py-0.5 rounded-full">
          {badge > 99 ? '99+' : badge}
        </span>
      )}
    </>
  );

  if (href) {
    return (
      <Link href={href} className={classes} onClick={onClick}>
        {inner}
      </Link>
    );
  }

  return (
    <button type="button" onClick={onClick} className={classes}>
      {inner}
    </button>
  );
}
