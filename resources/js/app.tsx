import { createInertiaApp } from '@inertiajs/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { ErrorBoundary } from '@/modules/shared/components/ErrorBoundary';
import { Layout } from '@/modules/shared/components/layouts/Layout';
import 'sonner/dist/styles.css';

type ComponenteConLayout = ComponentType<Record<string, unknown>> & {
    layout?:
        | ComponentType<{ children: ReactNode }>
        | ComponentType<{ children: ReactNode }>[]
        | ((page: ReactNode) => ReactNode);
};

const appName = import.meta.env.VITE_APP_NAME || 'Hotel Bugambilias';

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 1000 * 60 * 5, // 5 minutos de caché
            refetchOnWindowFocus: false,
            retry: 1,
        },
    },
});

const paginasGlob = import.meta.glob<{ default: ComponenteConLayout }>(
    './pages/**/*.tsx',
);

void createInertiaApp({
    strictMode: true,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        return resolvePageComponent(`./pages/${name}.tsx`, paginasGlob).then(
            (modulo) => {
                const page = modulo.default as ComponenteConLayout;

                if (page.layout === undefined) {
                    if (name.toLowerCase().startsWith('auth/')) {
                        page.layout = (pageContent: ReactNode) => pageContent;
                    } else {
                        page.layout = (pageContent: ReactNode) => (
                            <Layout>{pageContent}</Layout>
                        );
                    }
                }

                return page;
            },
        );
    },
    setup({ el, App, props }) {
        const content = (
            <QueryClientProvider client={queryClient}>
                <ErrorBoundary>
                    <App {...props} />
                </ErrorBoundary>
            </QueryClientProvider>
        );

        if (!el) {
            return content;
        }

        const root = createRoot(el);
        root.render(content);
    },
    progress: {
        color: '#d459ab',
    },
});
