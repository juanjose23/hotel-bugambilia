import { Link, usePage } from '@inertiajs/react';
import {
    BedDouble,
    LogOut,
    ExternalLink,
    HelpCircle,
    Phone,
    Building2,
} from 'lucide-react';
import { useState, useEffect } from 'react';
import type { ReactNode } from 'react';
import { NotificacionesFlash } from '@/modulos/compartido/componentes/NotificacionesFlash';

interface PropiedadesLayoutPortalCliente {
    children: ReactNode;
}

export const LayoutPortalCliente = ({
    children,
}: PropiedadesLayoutPortalCliente) => {
    const [desplazado, setDesplazado] = useState(false);
    const { props } = usePage();
    const auth = props.auth as
        { user?: { name?: string; email?: string } } | undefined;
    const hotel = props.hotel as
        { name?: string; telefono?: string } | undefined;

    const usuario = auth?.user;
    const nombreHotel = hotel?.name || 'Hotel Bugambilias';
    const telefonoContacto = hotel?.telefono || '+505 8713 6805';

    useEffect(() => {
        const manejarScroll = () => setDesplazado(window.scrollY > 15);
        window.addEventListener('scroll', manejarScroll);

        return () => window.removeEventListener('scroll', manejarScroll);
    }, []);

    return (
        <div className="flex min-h-screen flex-col bg-background font-sans selection:bg-bugambilia-500 selection:text-white">
            <NotificacionesFlash />

            {/* Cabecera Exclusiva del Portal de Huéspedes */}
            <header
                className={`sticky top-0 z-40 transition-all duration-300 ${
                    desplazado
                        ? 'border-b border-border/80 bg-background/95 shadow-sm backdrop-blur-md'
                        : 'bg-background'
                }`}
            >
                <div className="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    {/* Logotipo y Título del Portal */}
                    <div className="flex items-center gap-3">
                        <Link
                            href="/mis-reservas"
                            className="flex items-center gap-2.5 transition-opacity hover:opacity-90"
                        >
                            <div className="flex size-10 items-center justify-center rounded-2xl bg-gradient-to-br from-bugambilia-500 to-bugambilia-700 text-white shadow-xs">
                                <BedDouble className="size-5" />
                            </div>
                            <div>
                                <span className="block text-sm font-black tracking-tight text-foreground sm:text-base">
                                    {nombreHotel}
                                </span>
                                <span className="block text-[10px] font-bold text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                    Portal de Huéspedes
                                </span>
                            </div>
                        </Link>
                    </div>

                    {/* Acciones del Portal (Contacto, Volver a la Web, Info Usuario) */}
                    <div className="flex items-center gap-2 sm:gap-3">
                        <a
                            href={`tel:${telefonoContacto.replace(/\s+/g, '')}`}
                            className="hidden items-center gap-1.5 rounded-full border border-border/80 bg-card px-3.5 py-1.5 text-xs font-bold text-muted-foreground transition-colors hover:border-bugambilia-500/40 hover:text-foreground sm:flex"
                        >
                            <Phone className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span>{telefonoContacto}</span>
                        </a>

                        <a
                            href="/"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex items-center gap-1.5 rounded-full border border-border/80 bg-card px-3.5 py-1.5 text-xs font-bold text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            title="Ver sitio web del Hotel"
                        >
                            <Building2 className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span className="hidden sm:inline">
                                Sitio Web Hotel
                            </span>
                            <ExternalLink className="size-3" />
                        </a>

                        {usuario ? (
                            <div className="flex items-center gap-2 border-l border-border/60 pl-2">
                                <div className="hidden text-right md:block">
                                    <p className="text-xs font-extrabold text-foreground">
                                        {usuario.name}
                                    </p>
                                    <p className="text-[10px] font-medium text-muted-foreground">
                                        Huésped Autenticado
                                    </p>
                                </div>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="flex size-9 items-center justify-center rounded-xl border border-border/80 bg-card text-muted-foreground transition-colors hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30"
                                    title="Cerrar Sesión"
                                >
                                    <LogOut className="size-4" />
                                </Link>
                            </div>
                        ) : (
                            <div className="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                                <HelpCircle className="size-4 text-bugambilia-500" />
                                <span className="hidden md:inline">
                                    Acceso por Código o QR
                                </span>
                            </div>
                        )}
                    </div>
                </div>
            </header>

            {/* ÁREA PRINCIPAL DE CONTENIDO */}
            <main className="grow">{children}</main>

            {/* PIE DE PÁGINA LIMPIO DEL PORTAL */}
            <footer className="border-t border-border/60 bg-card py-6 text-center text-xs font-medium text-muted-foreground">
                <div className="container mx-auto px-4">
                    <p>
                        © {new Date().getFullYear()} {nombreHotel} — Sistema de
                        Gestión del Huésped & Auto Check-In
                    </p>
                </div>
            </footer>
        </div>
    );
};

export default LayoutPortalCliente;
