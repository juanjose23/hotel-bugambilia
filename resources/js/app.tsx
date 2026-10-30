import { createInertiaApp } from '@inertiajs/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { ErrorBoundary } from '@/modules/shared/components/ErrorBoundary';
import { Layout } from '@/modules/shared/components/layouts/Layout';
import { NavigationLoadingProvider } from '@/modules/shared/components/loading/NavigationLoadingProvider';
import {
    PageLoadError,
    PageSkeleton,
} from '@/modules/shared/components/skeletons/PageSkeleton';
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

const initialElement = document.getElementById('app');
const initialRoot = initialElement ? createRoot(initialElement) : undefined;
initialRoot?.render(
    <PageSkeleton
        href={window.location.href}
        layout={
            window.location.pathname.startsWith('/auth')
                ? 'auth'
                : window.location.pathname.startsWith('/portal')
                  ? 'portal'
                  : 'public'
        }
        initial
    />,
);

void createInertiaApp({
    strictMode: true,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        return resolvePageComponent(`./pages/${name}.tsx`, paginasGlob).then(
            (modulo) => {
                const page = modulo.default as ComponenteConLayout;

                if (page.layout === undefined) {
                    if (
                        name.toLowerCase().startsWith('auth/') ||
                        name.toLowerCase().startsWith('portal/')
                    ) {
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
                    <NavigationLoadingProvider>
                        <App {...props} />
                    </NavigationLoadingProvider>
                </ErrorBoundary>
            </QueryClientProvider>
        );

        if (!el) {
            return content;
        }

        const root = initialRoot ?? createRoot(el);
        root.render(content);
    },
    progress: {
        color: '#d459ab',
    },
}).catch(() => {
    initialRoot?.render(<PageLoadError />);
});
