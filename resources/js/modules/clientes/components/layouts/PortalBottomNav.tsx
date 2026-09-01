import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    CalendarDays,
    UserCircle,
    CalendarPlus,
} from 'lucide-react';

export const PortalBottomNav = () => {
    const url = usePage().url;

    const navItems = [
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
            nombre: 'Reservar',
            href: '/habitaciones',
            icono: CalendarPlus,
            exact: false,
        },
        {
            nombre: 'Perfil',
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

    return (
        <nav className="fixed inset-x-0 bottom-0 z-40 flex h-16 items-center justify-around border-t border-border/80 bg-background/95 backdrop-blur-lg lg:hidden">
            {navItems.map((item) => {
                const Icono = item.icono;
                const activo = esActivo(item.href, item.exact);

                return (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={`flex flex-col items-center justify-center gap-1 transition-all ${
                            activo
                                ? 'scale-105 font-bold text-primary'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Icono className="size-5" />
                        <span className="text-[10px]">{item.nombre}</span>
                    </Link>
                );
            })}
        </nav>
    );
};
