import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { User, AcademicYear } from './index';

// ─── Flash Messages ───────────────────────────────────────────────────────────

export interface Flash {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

// ─── Ziggy Route Config ───────────────────────────────────────────────────────

export interface ZiggyConfig {
  url: string;
  port: number | null;
  defaults: Record<string, string | number>;
  routes: Record<string, ZiggyRoute>;
  location?: ZiggyLocation;
}

export interface ZiggyRoute {
  uri: string;
  methods: string[];
  parameters?: string[];
  bindings?: Record<string, string>;
  wheres?: Record<string, string>;
  domain?: string | null;
}

export interface ZiggyLocation {
  host?: string;
  pathname?: string;
  search?: string;
}

// ─── Augment Inertia's PageProps ─────────────────────────────────────────────

declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps {
    auth: {
      user: User;
    };
    flash: Flash;
    activeYear?: AcademicYear | null;
    ziggy: ZiggyConfig;
  }
}

// ─── Global route() type ─────────────────────────────────────────────────────

declare global {
  function route(): ZiggyConfig;
  function route(name: string, params?: RouteParams, absolute?: boolean): string;
  function route(name?: undefined, params?: undefined, absolute?: undefined): ZiggyConfig;
}

export type RouteParams =
  | string
  | number
  | RouteParamsObject
  | (string | number)[];

export type RouteParamsObject = Record<
  string,
  string | number | boolean | null | undefined
>;
