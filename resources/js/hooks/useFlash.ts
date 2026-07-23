import { usePage } from '@inertiajs/react';
import type { Flash } from '@/types/inertia.d';

/**
 * React hook to read Inertia flash messages from shared props.
 *
 * Usage:
 *   const { flash } = useFlash();
 *   if (flash.success) { ... }
 */
export function useFlash(): Flash {
  const page = usePage();
  const props = page.props as { flash?: Flash };
  return props.flash ?? {};
}
