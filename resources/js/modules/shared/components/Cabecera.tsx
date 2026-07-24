import { Link, usePage } from '@inertiajs/react';
import {
    Menu,
    User,
    Phone,
    MapPin,
    Calendar,
    Search,
    BedDouble,
    MessageSquare,
    ConciergeBell,
    LogOut,
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { CambioTema } from '@/modules/shared/components/CambioTema';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
interface PropiedadesCabecera {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        direccion?: string;
    };
}
const navegacion = [
    { nombre: 'Inicio', href: '/' },
    { nombre: 'Habitaciones', href: '/habitaciones' },
    { nombre: 'Espacios', href: '/espacios' },
    { nombre: 'Servicios', href: '/servicios' },
    { nombre: 'Acerca de', href: '/acerca-de' },
    { nombre: 'Contacto', href: '/contacto' },
];
export const Cabecera = ({ hotelInfo }: PropiedadesCabecera) => {
    const [menuAbierto, setMenuAbierto] = useState(false);
    const [desplazado, setDesplazado] = useState(false);
    const { hotel, auth } = usePropiedadesPagina();
    const ruta = usePage().url;
    const nombreHotel = hotelInfo?.nombre || hotel?.name || 'Hotel Bugambilias';
    const telefono = hotelInfo?.telefono || hotel?.telefono || '+505 8713 6805';
    const usuarioAuth = auth?.user;
    const esAdmin = usuarioAuth?.is_admin === true;
    useEffect(() => {
        const manejarDesplazamiento = () => setDesplazado(window.scrollY > 20);
        window.addEventListener('scroll', manejarDesplazamiento);

        return () =>
            window.removeEventListener('scroll', manejarDesplazamiento);
    }, []);
    const elementosNavegacionMovil = [
        { etiqueta: 'Explorar', href: '/', icono: Search },
        { etiqueta: 'Habitaciones', href: '/habitaciones', icono: BedDouble },
        { etiqueta: 'Servicios', href: '/servicios', icono: ConciergeBell },
        { etiqueta: 'Contacto', href: '/contacto', icono: MessageSquare },
    ];

    return (
        <>
            {/* Barra superior de utilidades */}
            <div className="hidden border-b border-gray-800/80 bg-gray-950 py-2 font-sans text-xs font-medium text-white/90 md:block">
                <div className="container mx-auto flex items-center justify-between px-4 sm:px-6">
                    <div className="flex items-center gap-6">
                        <span className="flex items-center gap-1.5 font-bold tracking-wide text-amber-400">
                            <MapPin className="h-4 w-4 text-bugambilia-400" />
                            Estelí, Nicaragua
                        </span>
                        <span className="text-gray-700">|</span>
                        <a
                            href={`tel:${telefono.replace(/[^0-9+]/g, '')}`}
                            className="flex items-center gap-1.5 font-semibold transition-colors hover:text-white"
                        >
                            <Phone className="h-3.5 w-3.5 text-bugambilia-400" />
                            <span>{telefono}</span>
                        </a>
                    </div>
                    <div className="flex items-center gap-4 text-xs font-semibold text-gray-300">
                        <span className="flex items-center gap-1">
                            Salida Sur, Restaurante Absoluto 1c. O.
                        </span>
                    </div>
                </div>
            </div>

            {/* Barra de navegación principal sticky */}
            <header
                className={`sticky top-0 z-40 transition-all duration-200 ${
                    desplazado
                        ? 'luxury-glass shadow-airbnb border-b border-border/80 py-2.5'
                        : 'border-b border-border/40 bg-background/95 py-3.5 backdrop-blur-md'
                }`}
            >
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="flex h-14 items-center justify-between sm:h-16">
                        {/* Logo */}
                        <Link
                            href="/"
                            className="group flex shrink-0 items-center transition-opacity hover:opacity-95"
                        >
                            <img
                                src="/images/logo-dark.webp"
                                alt="Hotel Bugambilias"
                                className="h-10 w-auto object-contain sm:h-12 dark:hidden"
                            />
                            <img
                                src="/images/logo-claro.webp"
                                alt="Hotel Bugambilias"
                                className="hidden h-10 w-auto object-contain sm:h-12 dark:block"
                            />
                        </Link>

                        {/* Navegación de escritorio */}
                        <nav className="shadow-airbnb-subtle hidden items-center gap-1.5 rounded-full border border-border/70 bg-card/90 px-4 py-1.5 md:flex">
                            {navegacion.map((item) => {
                                const estaActiva =
                                    ruta === item.href ||
                                    (item.href !== '/' &&
                                        ruta.startsWith(item.href));

                                return (
                                    <Link
                                        key={item.nombre}
                                        href={item.href}
                                        className={`rounded-full px-4 py-1.5 text-xs font-extrabold transition-all duration-200 sm:text-sm ${
                                            estaActiva
                                                ? 'bg-bugambilia-600 text-white shadow-sm'
                                                : 'text-muted-foreground hover:bg-muted/70 hover:text-foreground'
                                        }`}
                                    >
                                        {item.nombre}
                                    </Link>
                                );
                            })}
                        </nav>

                        {/* Controles lado derecho */}
                        <div className="flex items-center gap-3">
                            <Link
                                href="/habitaciones"
                                className="shadow-airbnb hover:shadow-airbnb-hover hidden items-center gap-2 rounded-full bg-bugambilia-600 px-5 py-2.5 text-xs font-black tracking-wider text-white uppercase transition-all duration-200 hover:scale-105 hover:bg-bugambilia-700 sm:inline-flex sm:text-sm"
                            >
                                <Calendar className="h-4 w-4" />
                                <span>Reservar</span>
                            </Link>

                            <CambioTema />

                            {/* Menú de usuario */}
                            <div className="relative">
                                <button
                                    onClick={() => setMenuAbierto(!menuAbierto)}
                                    aria-label="Abrir menú"
                                    className="shadow-airbnb-subtle hover:shadow-airbnb flex cursor-pointer items-center gap-2.5 rounded-full border border-border bg-card p-1.5 pl-3 transition-all hover:border-gray-400 dark:hover:border-gray-600"
                                >
                                    <Menu className="h-4 w-4 text-foreground" />
                                    <div className="rounded-full bg-bugambilia-600 p-1.5 text-white shadow-sm">
                                        <User className="h-4 w-4" />
                                    </div>
                                </button>

                                {menuAbierto && (
                                    <>
                                        <div
                                            className="fixed inset-0 z-40"
                                            onClick={() =>
                                                setMenuAbierto(false)
                                            }
                                        />
                                        <div className="shadow-airbnb-hover animate-in fade-in zoom-in-95 absolute right-0 z-50 mt-2 w-64 rounded-2xl border border-border/80 bg-card py-2 duration-200">
                                            {usuarioAuth ? (
                                                <>
                                                    <div className="mb-1 border-b border-border/60 px-4 py-2.5">
                                                        <p className="text-sm font-black text-foreground">
                                                            {usuarioAuth.name}
                                                        </p>
                                                        <p className="truncate text-xs text-muted-foreground">
                                                            {usuarioAuth.email}
                                                        </p>
                                                    </div>
                                                    <Link
                                                        href="/logout"
                                                        method="post"
                                                        as="button"
                                                        onClick={() =>
                                                            setMenuAbierto(
                                                                false,
                                                            )
                                                        }
                                                        className="mx-1.5 flex w-full cursor-pointer items-center gap-2 rounded-xl px-4 py-2.5 text-left text-xs font-bold text-rose-500 transition-colors hover:bg-muted/70"
                                                    >
                                                        <LogOut className="h-3.5 w-3.5" />
                                                        <span>
                                                            Cerrar Sesión
                                                        </span>
                                                    </Link>
                                                </>
                                            ) : (
                                                <>
                                                    <div className="mb-1 border-b border-border/60 px-4 py-2.5">
                                                        <p className="text-sm font-extrabold text-foreground">
                                                            {nombreHotel}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            Estelí, Nicaragua
                                                        </p>
                                                    </div>
                                                    <Link
                                                        href="/login"
                                                        onClick={() =>
                                                            setMenuAbierto(
                                                                false,
                                                            )
                                                        }
                                                        className="mx-1.5 block rounded-xl px-4 py-2.5 text-xs font-bold text-foreground transition-colors hover:bg-muted/70"
                                                    >
                                                        Iniciar Sesión
                                                    </Link>
                                                    <Link
                                                        href="/registro"
                                                        onClick={() =>
                                                            setMenuAbierto(
                                                                false,
                                                            )
                                                        }
                                                        className="mx-1.5 block rounded-xl px-4 py-2.5 text-xs font-bold text-bugambilia-600 transition-colors hover:bg-muted/70 dark:text-bugambilia-400"
                                                    >
                                                        Crear Cuenta
                                                    </Link>
                                                </>
                                            )}

                                            {esAdmin && (
                                                <Link
                                                    href="/admin"
                                                    onClick={() =>
                                                        setMenuAbierto(false)
                                                    }
                                                    className="mx-1.5 block rounded-xl px-4 py-2.5 text-xs font-semibold text-muted-foreground transition-colors hover:bg-muted/70"
                                                >
                                                    Portal Administración
                                                </Link>
                                            )}
                                            {esAdmin && (
                                                <div className="mx-3 my-1 h-px bg-border/60" />
                                            )}
                                            {navegacion.map((item) => (
                                                <Link
                                                    key={item.nombre}
                                                    href={item.href}
                                                    onClick={() =>
                                                        setMenuAbierto(false)
                                                    }
                                                    className="mx-1.5 block rounded-xl px-4 py-2 text-xs font-semibold transition-colors hover:bg-muted/70 sm:text-sm"
                                                >
                                                    {item.nombre}
                                                </Link>
                                            ))}
                                            <div className="mx-3 my-1 h-px bg-border/60" />
                                            <Link
                                                href="/habitaciones"
                                                onClick={() =>
                                                    setMenuAbierto(false)
                                                }
                                                className="mx-2 my-1 block rounded-xl bg-bugambilia-600 px-4 py-2.5 text-center text-xs font-bold text-white sm:text-sm"
                                            >
                                                Ver Disponibilidad
                                            </Link>
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {/* Barra de navegación móvil inferior flotante */}
            <div className="fixed right-0 bottom-0 left-0 z-50 border-t border-border/80 bg-background/95 px-3 py-2 font-sans shadow-2xl backdrop-blur-xl md:hidden">
                <div className="flex items-center justify-around">
                    {elementosNavegacionMovil.map((item) => {
                        const Icono = item.icono;
                        const estaActiva =
                            ruta === item.href ||
                            (item.href !== '/' && ruta.startsWith(item.href));

                        return (
                            <Link
                                key={item.etiqueta}
                                href={item.href}
                                className={`flex flex-col items-center rounded-2xl px-3 py-1 transition-all duration-200 ${
                                    estaActiva
                                        ? 'font-bold text-bugambilia-600 dark:text-bugambilia-400'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Icono
                                    className={`mb-0.5 h-5 w-5 ${estaActiva ? 'scale-110' : ''}`}
                                />
                                <span className="text-[11px] font-semibold tracking-tight">
                                    {item.etiqueta}
                                </span>
                            </Link>
                        );
                    })}

                    <button
                        onClick={() => setMenuAbierto(!menuAbierto)}
                        className={`flex flex-col items-center rounded-2xl px-3 py-1 transition-all duration-200 ${
                            menuAbierto
                                ? 'font-bold text-bugambilia-600 dark:text-bugambilia-400'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <User className="mb-0.5 h-5 w-5" />
                        <span className="text-[11px] font-semibold tracking-tight">
                            Menú
                        </span>
                    </button>
                </div>
            </div>
        </>
    );
};
