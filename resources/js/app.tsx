import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';
import { LayoutPublico } from '@/modulos/compartido/componentes/layouts/LayoutPublico';
import { LimitadorErrores } from '@/modulos/compartido/componentes/LimitadorErrores';
import { ProveedorTema } from '@/modulos/compartido/hooks/useTema';
import 'sonner/dist/styles.css';
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
