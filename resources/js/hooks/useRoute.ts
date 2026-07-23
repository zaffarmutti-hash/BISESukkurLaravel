import type { RouteParams } from '@/types/inertia.d';

/**
 * React hook that returns the Ziggy-powered route() helper.
 * Ziggy is loaded globally from the blade template via @routes directive.
 *
 * Usage:
 *   const { route } = useRoute();
 *   route('school.dashboard')          → '/school/dashboard'
 *   route('school.students.show', 123) → '/school/students/123'
 */
export function useRoute() {
  // Validate Ziggy is available globally
  if (typeof window !== 'undefined' && typeof (window as Window & { route?: unknown }).route !== 'function') {
    console.warn('[useRoute] Ziggy route() not found. Ensure @routes is in your Blade layout.');
  }

  const routeFn = (window as Window & { route: typeof route }).route;

  return { route: routeFn };
}

/**
 * Standalone route() helper — use this outside of React components.
 * Falls back gracefully if Ziggy is not yet loaded.
 */
export function resolveRoute(name: string, params?: RouteParams): string {
  const fn = (window as Window & { route?: (name: string, params?: RouteParams) => string }).route;
  if (!fn) {
    console.error(`[resolveRoute] route('${name}') called before Ziggy loaded`);
    return '#';
  }
  return fn(name, params);
}
