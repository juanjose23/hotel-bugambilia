import type { ReactNode } from 'react';
import { Cabecera } from '@/modulos/compartido/componentes/Cabecera';
import { NotificacionesFlash } from '@/modulos/compartido/componentes/NotificacionesFlash';
import { PiePagina } from '@/modulos/compartido/componentes/PiePagina';
interface PropiedadesLayoutPublico {
    children: ReactNode;
}
export const LayoutPublico = ({ children }: PropiedadesLayoutPublico) => {
    return (
        <div className="flex min-h-screen flex-col justify-between bg-background font-sans selection:bg-bugambilia-500 selection:text-white">
            <NotificacionesFlash />
            <Cabecera />
            <main className="grow">{children}</main>
            <PiePagina />
        </div>
    );
};
