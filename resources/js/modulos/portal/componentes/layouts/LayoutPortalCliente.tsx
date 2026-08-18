import { Link, usePage } from '@inertiajs/react';
import {
    BedDouble,
    LogOut,
    ExternalLink,
    HelpCircle,
    Phone,
    Building2,
    Home,
    Utensils,
    Bell,
    User,
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
    const { props, url } = usePage();
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
        <div className="relative flex min-h-screen w-full max-w-full flex-col overflow-x-hidden bg-background font-sans selection:bg-bugambilia-500 selection:text-white">
            <NotificacionesFlash />

            {/* Cabecera Exclusiva del Portal de Huéspedes */}
            <header
                className={`sticky top-0 z-40 w-full max-w-full transition-all duration-300 ${
                    desplazado
                        ? 'border-b border-border/80 bg-background/95 shadow-sm backdrop-blur-md'
                        : 'bg-background'
                }`}
            >
                <div className="container mx-auto flex h-16 max-w-full items-center justify-between px-3 sm:px-6 lg:px-8">
                    {/* Logotipo y Título del Portal */}
                    <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                        <Link
                            href="/portal"
                            className="flex items-center gap-2 transition-opacity hover:opacity-90"
                        >
                            <div className="flex size-9 items-center justify-center rounded-2xl bg-gradient-to-br from-bugambilia-500 to-bugambilia-700 text-white shadow-xs sm:size-10">
                                <BedDouble className="size-4.5 sm:size-5" />
                            </div>
                            <div>
                                <span className="block text-xs font-black tracking-tight text-foreground sm:text-base">
                                    {nombreHotel}
                                </span>
                                <span className="block text-[9px] font-bold text-bugambilia-600 uppercase sm:text-[10px] dark:text-bugambilia-400">
                                    Portal de Huéspedes
                                </span>
                            </div>
                        </Link>
                    </div>

                    {/* Acciones de Cabecera (Contacto, Sitio Web, Usuario) */}
                    <div className="flex items-center gap-1.5 sm:gap-2">
                        <a
                            href={`tel:${telefonoContacto.replace(/\s+/g, '')}`}
                            className="hidden items-center gap-1.5 rounded-full border border-border/80 bg-card px-3 py-1 text-xs font-bold text-muted-foreground transition-colors hover:border-bugambilia-500/40 hover:text-foreground md:flex"
                        >
                            <Phone className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span>{telefonoContacto}</span>
                        </a>

                        <a
                            href="/"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hidden items-center gap-1.5 rounded-full border border-border/80 bg-card px-3 py-1 text-xs font-bold text-muted-foreground transition-colors hover:bg-muted hover:text-foreground sm:flex"
                            title="Ver sitio web del Hotel"
                        >
                            <Building2 className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span>Sitio Web</span>
                            <ExternalLink className="size-3" />
                        </a>

                        {usuario ? (
                            <div className="flex items-center gap-1.5 border-l border-border/60 pl-1.5 sm:gap-2 sm:pl-2">
                                <div className="hidden text-right lg:block">
                                    <p className="text-xs font-extrabold text-foreground truncate max-w-[120px]">
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
                                    className="flex size-8 items-center justify-center rounded-xl border border-border/80 bg-card text-muted-foreground transition-colors hover:bg-rose-50 hover:text-rose-600 sm:size-9 dark:hover:bg-rose-950/30"
                                    title="Cerrar Sesión"
                                >
                                    <LogOut className="size-4" />
                                </Link>
                            </div>
                        ) : (
                            <Link
                                href="/login"
                                className="flex items-center gap-1 rounded-full bg-bugambilia-600/10 px-2.5 py-1 text-[11px] font-extrabold text-bugambilia-600 dark:text-bugambilia-400 sm:px-3 sm:text-xs"
                            >
                                <User className="size-3.5" />
                                <span>Ingresar</span>
                            </Link>
                        )}
                    </div>
                </div>
            </header>

            {/* ÁREA PRINCIPAL DE CONTENIDO */}
            <main className="grow w-full max-w-full pb-20 md:pb-0">{children}</main>

            {/* BARRA DE NAVEGACIÓN INFERIOR ESTILO APP MÓVIL (SOLO MÓVIL, SIEMPRE VISIBLE Y ESTÁTICA) */}
            <nav className="fixed bottom-0 left-0 right-0 z-[9999] w-full border-t border-border bg-card py-1.5 shadow-2xl md:hidden">
                <div className="grid grid-cols-5 w-full max-w-lg mx-auto items-center justify-items-center text-center">
                    <Link
                        href="/portal"
                        className={`flex w-full flex-col items-center justify-center gap-0.5 py-1 text-center transition-colors ${
                            url === '/portal' || url === '/mis-reservas'
                                ? 'text-bugambilia-600 dark:text-bugambilia-400 font-black'
                                : 'text-muted-foreground hover:text-foreground font-semibold'
                        }`}
                    >
                        <Home className="size-5 shrink-0" />
                        <span className="text-[9px] min-[360px]:text-[10px] tracking-tight truncate max-w-full px-0.5">Reservas</span>
                    </Link>

                    <Link
                        href="/habitaciones"
                        className={`flex w-full flex-col items-center justify-center gap-0.5 py-1 text-center transition-colors ${
                            url.startsWith('/habitaciones')
                                ? 'text-bugambilia-600 dark:text-bugambilia-400 font-black'
                                : 'text-muted-foreground hover:text-foreground font-semibold'
                        }`}
                    >
                        <BedDouble className="size-5 shrink-0" />
                        <span className="text-[9px] min-[360px]:text-[10px] tracking-tight truncate max-w-full px-0.5">Habitaciones</span>
                    </Link>

                    <Link
                        href="/servicios"
                        className={`flex w-full flex-col items-center justify-center gap-0.5 py-1 text-center transition-colors ${
                            url.startsWith('/servicios')
                                ? 'text-bugambilia-600 dark:text-bugambilia-400 font-black'
                                : 'text-muted-foreground hover:text-foreground font-semibold'
                        }`}
                    >
                        <Bell className="size-5 shrink-0" />
                        <span className="text-[9px] min-[360px]:text-[10px] tracking-tight truncate max-w-full px-0.5">Servicios</span>
                    </Link>

                    <Link
                        href="/restaurante"
                        className={`flex w-full flex-col items-center justify-center gap-0.5 py-1 text-center transition-colors ${
                            url.startsWith('/restaurante')
                                ? 'text-bugambilia-600 dark:text-bugambilia-400 font-black'
                                : 'text-muted-foreground hover:text-foreground font-semibold'
                        }`}
                    >
                        <Utensils className="size-5 shrink-0" />
                        <span className="text-[9px] min-[360px]:text-[10px] tracking-tight truncate max-w-full px-0.5">Restaurante</span>
                    </Link>

                    <Link
                        href={usuario ? '/portal/cuenta' : '/login'}
                        className={`flex w-full flex-col items-center justify-center gap-0.5 py-1 text-center transition-colors ${
                            url.startsWith('/portal/cuenta') || url.startsWith('/login')
                                ? 'text-bugambilia-600 dark:text-bugambilia-400 font-black'
                                : 'text-muted-foreground hover:text-foreground font-semibold'
                        }`}
                    >
                        <User className="size-5 shrink-0" />
                        <span className="text-[9px] min-[360px]:text-[10px] tracking-tight truncate max-w-full px-0.5">
                            {usuario ? 'Mi Cuenta' : 'Ingresar'}
                        </span>
                    </Link>
                </div>
            </nav>

            {/* PIE DE PÁGINA LIMPIO DEL PORTAL */}
            <footer className="border-t border-border/60 bg-card py-6 text-center text-xs font-medium text-muted-foreground mb-16 md:mb-0">
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
