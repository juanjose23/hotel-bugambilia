import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType, ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';
import { LayoutPublico } from '@/modulos/compartido/componentes/layouts/LayoutPublico';
import { LimitadorErrores } from '@/modulos/compartido/componentes/LimitadorErrores';
import { ProveedorTema } from '@/modulos/compartido/hooks/useTema';
import 'sonner/dist/styles.css';

type ComponenteConLayout = ComponentType<Record<string, unknown>> & {
    layout?:
        | ComponentType<{ children: ReactNode }>
        | ComponentType<{ children: ReactNode }>[]
        | ((page: ReactNode) => ReactNode);
};

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    strictMode: true,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob<{ default: ComponenteConLayout }>(
                './pages/**/*.tsx',
            ),
        ).then((modulo) => {
            const page = modulo.default as ComponenteConLayout;
            page.layout =
                page.layout ||
                ((pageContent: ReactNode) => (
                    <LayoutPublico>{pageContent}</LayoutPublico>
                ));

            return page;
        }),
    setup({ el, App, props }) {
        const content = (
            <ProveedorTema>
                <LimitadorErrores>
                    <App {...props} />
                    <Toaster richColors position="top-right" />
                </LimitadorErrores>
            </ProveedorTema>
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
