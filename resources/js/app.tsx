/// <reference types="vite/client" />
import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = (import.meta.env['VITE_APP_NAME'] as string | undefined) ?? 'BISE Sukkur';

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),

    // Pages are resolved from resources/js/pages/ directory.
    // .tsx files match Inertia::render() calls.
    // e.g. Inertia::render('admin/Dashboard') → pages/admin/Dashboard.tsx
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (import.meta as any).glob('./pages/**/*.tsx') as Record<string, () => Promise<unknown>>,
        ),

    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },

    // Show progress bar during Inertia navigations
    progress: {
        color: '#f59e0b', // amber — matches Super Admin accent
        showSpinner: false,
    },
});
