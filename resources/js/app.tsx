import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import { LayoutPublico } from '@/modules/shared/components/layouts/LayoutPublico';
import { LimitadorErrores } from '@/modules/shared/components/LimitadorErrores';
import { ProveedorTema } from '@/modules/shared/hooks/useTema';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
void createInertiaApp({
    strictMode: true,
    layout: () => LayoutPublico,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const paginas = import.meta.glob<{
            default: ComponentType;
        }>('./pages/**/*.tsx');
        const pagina = paginas[`./pages/${name}.tsx`];

        if (!pagina) {
            throw new Error(`Página Inertia no registrada: ${name}`);
        }

        const modulo = await pagina();

        return modulo.default;
    },
    setup({ el, App, props }) {
        const content = (
            <ProveedorTema>
                <LimitadorErrores>
                    <App {...props} />
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
