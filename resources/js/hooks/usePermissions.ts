import { usePage } from '@inertiajs/react';

/**
 * React hook equivalent of the Vue usePermissions composable.
 * Reads the user's permission array from Inertia shared props.
 */
export function usePermissions() {
  const { auth } = usePage().props as unknown as {
    auth: { user: { permissions: string[] } };
  };

  const permissions: string[] = auth?.user?.permissions ?? [];

  /**
   * Check if the current user has a given permission (or any of an array).
   */
  function can(permission: string | string[]): boolean {
    if (!permission) return true;
    if (Array.isArray(permission)) {
      return permission.some((p) => permissions.includes(p));
    }
    return permissions.includes(permission);
  }

  return { can, permissions };
}
