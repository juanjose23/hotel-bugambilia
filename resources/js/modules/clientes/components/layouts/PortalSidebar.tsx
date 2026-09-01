import { Link, router, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    CalendarDays,
    CalendarPlus,
    UserCircle,
    LogOut,
    Sparkles,
    Sun,
    Moon,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { useTema } from '@/modules/shared/hooks/useTema';
import type { ClienteProfile } from '../../types';

interface PortalSidebarProps {
    cliente?: ClienteProfile;
}

export const PortalSidebar = ({ cliente }: PortalSidebarProps) => {
    const url = usePage().url;
    const { tema, alternarTema } = useTema();

    const enlaces = [
        {
            nombre: 'Dashboard',
            href: '/portal',
            icono: LayoutDashboard,
            exact: true,
        },
        {
            nombre: 'Mis Reservas',
            href: '/portal/reservas',
            icono: CalendarDays,
            exact: false,
        },
        {
            nombre: 'Reservar Suite',
            href: '/habitaciones',
            icono: CalendarPlus,
            exact: false,
        },
        {
            nombre: 'Mi Perfil',
            href: '/portal/perfil',
            icono: UserCircle,
            exact: true,
        },
    ];

    const esActivo = (href: string, exact: boolean) => {
        if (exact) {
            return url === href;
        }

        return url.startsWith(href);
    };

    const iniciales = cliente?.nombre
        ? cliente.nombre
              .split(' ')
              .map((n) => n[0])
              .slice(0, 2)
              .join('')
              .toUpperCase()
        : 'HB';

    return (
        <aside className="hidden w-72 flex-col justify-between border-r border-border/70 bg-card p-6 lg:flex">
            {/* Header del Sidebar */}
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Link href="/portal" className="flex items-center gap-2.5">
                        <img
                            src="/images/logo-dark.webp"
                            alt="Hotel Bugambilias"
                            className="hidden h-10 w-auto object-contain dark:block"
                        />
                        <img
                            src="/images/logo-white.webp"
                            alt="Hotel Bugambilias"
                            className="block h-10 w-auto object-contain dark:hidden"
                        />
                    </Link>
                </div>

                {/* Perfil del Huésped */}
                <div className="flex items-center gap-3.5 rounded-2xl border border-border/40 bg-secondary/40 p-3.5">
                    <div className="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-base font-bold text-white shadow-sm">
                        {iniciales}
                    </div>
                    <div className="min-w-0 flex-1">
                        <h4 className="truncate text-sm font-bold text-foreground">
                            {cliente?.nombre || 'Huésped'}
                        </h4>
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Sparkles className="size-3 text-amber-500" />
                            <span className="truncate">
                                {cliente?.tipo_cliente || 'Cliente VIP'}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Menú de Navegación */}
                <nav className="space-y-1.5 pt-2">
                    <div className="px-3 pb-2 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                        Portal de Huéspedes
                    </div>
                    {enlaces.map((enlace) => {
                        const Icono = enlace.icono;
                        const activo = esActivo(enlace.href, enlace.exact);

                        return (
                            <Link
                                key={enlace.href}
                                href={enlace.href}
                                className={`flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all ${
                                    activo
                                        ? 'bg-primary text-white shadow-sm shadow-primary/20'
                                        : 'text-muted-foreground hover:bg-secondary/60 hover:text-foreground'
                                }`}
                            >
                                <Icono className="size-4.5" />
                                <span>{enlace.nombre}</span>
                            </Link>
                        );
                    })}
                </nav>
            </div>

            {/* Footer del Sidebar */}
            <div className="space-y-3 border-t border-border/50 pt-6">
                <div className="flex items-center justify-between gap-2 pt-1">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={alternarTema}
                        className="flex-1 justify-start gap-2 text-xs text-muted-foreground"
                    >
                        {tema === 'dark' ? (
                            <>
                                <Sun className="size-4 text-amber-400" />
                                <span>Modo Claro</span>
                            </>
                        ) : (
                            <>
                                <Moon className="size-4" />
                                <span>Modo Oscuro</span>
                            </>
                        )}
                    </Button>

                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => router.post('/auth/logout')}
                        className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        title="Cerrar sesión"
                    >
                        <LogOut className="size-4" />
                    </Button>
                </div>
            </div>
        </aside>
    );
};
