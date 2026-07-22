import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from '@/modules/shared/hooks/useTheme';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx');
        const modules = import.meta.glob('./modules/**/pages/**/*.tsx');

        if (name.includes('/')) {
            const [module, page] = name.split('/');

            return resolvePageComponent(
                `./modules/${module}/pages/${page}.tsx`,
                modules,
            ) as any;
        }

        return resolvePageComponent(`./pages/${name}.tsx`, pages) as any;
    },
    setup({ el, App, props }) {
        if (!el) {
            return (
                <ThemeProvider>
                    <App {...props} />
                </ThemeProvider>
            );
        }

        const root = createRoot(el);
        root.render(
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>,
        );
    },
    progress: {
        color: '#d459ab',
    },
});
