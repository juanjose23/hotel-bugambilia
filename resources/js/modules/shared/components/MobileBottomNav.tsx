import { Link, usePage } from '@inertiajs/react';
import {
    Home,
    BedDouble,
    Landmark,
    UtensilsCrossed,
    Users,
    MessageSquare,
} from 'lucide-react';

export const MobileBottomNav = () => {
    const { url } = usePage();

    const items = [
        {
            nombre: 'Inicio',
            href: '/',
            icono: Home,
            activo: url === '/' || url === '',
        },
        {
            nombre: 'Habitaciones',
            href: '/habitaciones',
            icono: BedDouble,
            activo: url.startsWith('/habitaciones'),
        },
        {
            nombre: 'Espacios',
            href: '/espacios',
            icono: Landmark,
            activo: url.startsWith('/espacios'),
        },
        {
            nombre: 'Servicios',
            href: '/servicios',
            icono: UtensilsCrossed,
            activo: url.startsWith('/servicios'),
        },
        {
            nombre: 'Nosotros',
            href: '/acerca-de',
            icono: Users,
            activo: url.startsWith('/acerca-de'),
        },
        {
            nombre: 'Contacto',
            href: '/contacto',
            icono: MessageSquare,
            activo: url.startsWith('/contacto'),
        },
    ];

    return (
        <aside
            aria-label="Barra de navegación móvil dock"
            style={{
                position: 'fixed',
                bottom: '10px',
                left: '10px',
                right: '10px',
                zIndex: 99999,
            }}
            className="block md:hidden"
        >
            <nav className="relative flex h-14 items-center justify-between rounded-full border border-border/80 bg-card/95 px-2 shadow-2xl backdrop-blur-xl dark:border-border/60 dark:bg-card/90 dark:shadow-[0_10px_30px_rgba(0,0,0,0.8)]">
                {items.map((item) => {
                    const Icono = item.icono;

                    // Si este ítem está seleccionado, sobresale dinámicamente hacia arriba
                    if (item.activo) {
                        return (
                            <Link
                                key={item.nombre}
                                href={item.href}
                                aria-label={`${item.nombre} (Página activa)`}
                                className="group relative -top-3.5 flex flex-1 flex-col items-center justify-center transition-all duration-300 active:scale-95"
                            >
                                <div className="flex size-12 items-center justify-center rounded-full border-2 border-background bg-primary text-primary-foreground shadow-lg ring-2 ring-primary/40 transition-all duration-300">
                                    <Icono
                                        className="size-5.5 stroke-[2.4]"
                                        aria-hidden="true"
                                    />
                                </div>
                                <span className="mt-0.5 text-[9px] font-black tracking-tight text-primary dark:text-rose-400">
                                    {item.nombre}
                                </span>
                            </Link>
                        );
                    }

                    // Ítems no seleccionados (planos y compactos)
                    return (
                        <Link
                            key={item.nombre}
                            href={item.href}
                            prefetch
                            aria-label={item.nombre}
                            className="group flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-muted-foreground transition-all duration-200 hover:text-foreground active:scale-90"
                        >
                            <Icono
                                className="size-4.5 stroke-[1.8] transition-transform group-hover:-translate-y-0.5"
                                aria-hidden="true"
                            />
                            <span className="text-[9px] font-medium tracking-tight">
                                {item.nombre}
                            </span>
                        </Link>
                    );
                })}
            </nav>
        </aside>
    );
};

export default MobileBottomNav;
