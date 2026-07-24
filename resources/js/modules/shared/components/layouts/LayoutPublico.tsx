import type { ReactNode } from 'react';
import { Cabecera } from '@/modules/shared/components/Cabecera';
import { PiePagina } from '@/modules/shared/components/PiePagina';
interface PropiedadesLayoutPublico {
    children: ReactNode;
}
export const LayoutPublico = ({ children }: PropiedadesLayoutPublico) => {
    return (
        <div className="flex min-h-screen flex-col justify-between bg-background font-sans selection:bg-bugambilia-500 selection:text-white">
            <Cabecera />
            <main className="grow">{children}</main>
            <PiePagina />
        </div>
    );
};
