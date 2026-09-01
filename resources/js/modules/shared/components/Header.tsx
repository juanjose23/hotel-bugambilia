import { Link, router, usePage } from '@inertiajs/react';
import {
    Menu,
    Moon,
    Sun,
    CalendarCheck,
    LogIn,
    LayoutDashboard,
    LogOut,
    KeyRound,
    BedDouble,
    ChevronDown,
    Shield,
    Sparkles,
} from 'lucide-react';
import { Button, buttonVariants } from '@/modules/shared/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/modules/shared/components/ui/dropdown-menu';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
import { useTema } from '@/modules/shared/hooks/useTema';

const enlaces = [
    { nombre: 'Inicio', href: '/' },
    { nombre: 'Habitaciones', href: '/habitaciones' },
    { nombre: 'Promociones', href: '/promociones' },
    { nombre: 'Espacios', href: '/espacios' },
    { nombre: 'Servicios', href: '/servicios' },
    { nombre: 'Nosotros', href: '/acerca-de' },
    { nombre: 'Contacto', href: '/contacto' },
];

export const Header = () => {
    const ruta = usePage().url;
    const { tema, alternarTema } = useTema();
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;

    const iniciales = usuario?.name
        ? usuario.name
              .split(' ')
              .map((n) => n[0])
              .slice(0, 2)
              .join('')
              .toUpperCase()
        : 'HB';

    return (
        <header className="sticky top-0 z-50 border-b border-border/60 bg-background/95 backdrop-blur-md">
            <div className="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6">
                {/* Logo */}
                <Link href="/" className="flex items-center gap-2">
                    <img
                        src="/images/logo-dark.webp"
                        alt="Hotel Bugambilias"
                        className="h-10 w-auto object-contain dark:hidden"
                    />
                    <img
                        src="/images/logo-claro.webp"
                        alt="Hotel Bugambilias"
                        className="hidden h-10 w-auto object-contain dark:block"
                    />
                </Link>

                {/* Navegación Desktop */}
                <nav className="hidden items-center gap-1 rounded-full border border-border/80 bg-card/80 px-3 py-1 shadow-xs md:flex">
                    {enlaces.map((item) => {
                        const activo =
                            ruta === item.href ||
                            (item.href !== '/' && ruta.startsWith(item.href));

                        return (
                            <Link
                                key={item.nombre}
                                href={item.href}
                                prefetch
                                className={buttonVariants({
                                    variant: activo ? 'default' : 'ghost',
                                    size: 'sm',
                                    className:
                                        'rounded-full px-4 text-xs font-bold',
                                })}
                            >
                                {item.nombre}
                            </Link>
                        );
                    })}
                </nav>

                {/* Acciones */}
                <div className="flex items-center gap-2.5">
                    {/* Botón Alternar Tema */}
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={alternarTema}
                        aria-label="Alternar tema"
                        className="cursor-pointer rounded-full transition-transform active:scale-95"
                    >
                        {tema === 'dark' ? (
                            <Sun className="size-4 text-amber-400" />
                        ) : (
                            <Moon className="size-4 text-slate-700 dark:text-slate-200" />
                        )}
                    </Button>

                    {/* Menú de Usuario Autenticado / Botón Iniciar Sesión (Desktop) */}
                    {usuario ? (
                        <div className="hidden sm:block">
                            <DropdownMenu>
                                <DropdownMenuTrigger className="group inline-flex cursor-pointer items-center gap-2.5 rounded-full border border-border/80 bg-card/90 py-1 pr-3 pl-1.5 shadow-xs transition-all hover:border-primary/40 hover:bg-card focus:outline-none">
                                    {/* Avatar con degradado y dot de estado */}
                                    <div className="relative flex size-7 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-[11px] font-black text-white shadow-xs">
                                        {iniciales}
                                        <span className="absolute -right-0.5 -bottom-0.5 size-2.5 rounded-full bg-emerald-500 ring-2 ring-card" />
                                    </div>
                                    <div className="flex flex-col text-left">
                                        <span className="max-w-28 truncate text-xs font-bold text-foreground">
                                            {
                                                (
                                                    usuario.name || 'Huésped'
                                                ).split(' ')[0]
                                            }
                                        </span>
                                        <span className="text-[9px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                                            {usuario.is_admin ? 'Admin' : 'VIP'}
                                        </span>
                                    </div>
                                    <ChevronDown className="size-3 text-muted-foreground transition-transform group-hover:translate-y-0.5" />
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-64 border-border/80 p-2 font-sans shadow-xl"
                                >
                                    {/* Tarjeta de Perfil del Usuario */}
                                    <DropdownMenuLabel className="rounded-xl bg-muted/40 p-3">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-9 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-xs font-black text-white shadow-xs">
                                                {iniciales}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-1.5">
                                                    <p className="truncate text-xs font-bold text-foreground">
                                                        {usuario.name ||
                                                            'Huésped'}
                                                    </p>
                                                </div>
                                                <p className="truncate text-[11px] text-muted-foreground">
                                                    {usuario.email}
                                                </p>
                                                <span className="mt-1 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-black text-primary dark:text-rose-300">
                                                    {usuario.is_admin ? (
                                                        <>
                                                            <Shield className="size-2.5" />
                                                            <span>
                                                                Administrador
                                                            </span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <Sparkles className="size-2.5" />
                                                            <span>
                                                                Huésped VIP
                                                            </span>
                                                        </>
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator className="my-1.5" />

                                    {usuario.is_admin && (
                                        <DropdownMenuItem
                                            onClick={() =>
                                                router.visit('/admin')
                                            }
                                            className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-primary focus:bg-primary/10 dark:text-rose-400"
                                        >
                                            <LayoutDashboard className="size-4" />
                                            <span>Panel de Control Admin</span>
                                        </DropdownMenuItem>
                                    )}

                                    <DropdownMenuItem
                                        onClick={() => router.visit('/portal')}
                                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-primary focus:bg-primary/10"
                                    >
                                        <LayoutDashboard className="size-4 text-primary" />
                                        <span>Portal de Huéspedes</span>
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        onClick={() =>
                                            router.visit('/portal/reservas')
                                        }
                                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-medium text-foreground focus:bg-muted"
                                    >
                                        <BedDouble className="size-4 text-muted-foreground" />
                                        <span>Mis Reservas & Estancias</span>
                                    </DropdownMenuItem>

                                    <DropdownMenuItem
                                        onClick={() =>
                                            router.visit(
                                                '/auth/cambiar-contrasena',
                                            )
                                        }
                                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-medium text-foreground focus:bg-muted"
                                    >
                                        <KeyRound className="size-4 text-muted-foreground" />
                                        <span>Seguridad & Contraseña</span>
                                    </DropdownMenuItem>

                                    <DropdownMenuSeparator className="my-1.5" />

                                    <DropdownMenuItem
                                        variant="destructive"
                                        onClick={() =>
                                            router.post('/auth/logout')
                                        }
                                        className="cursor-pointer rounded-lg px-2.5 py-2 text-xs font-bold text-destructive focus:bg-destructive/10"
                                    >
                                        <LogOut className="size-4" />
                                        <span>Cerrar Sesión</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    ) : (
                        <Link
                            href="/auth/login"
                            className={buttonVariants({
                                variant: 'ghost',
                                size: 'sm',
                                className:
                                    'hidden items-center gap-1.5 rounded-full px-3 text-xs font-bold text-muted-foreground hover:text-foreground sm:inline-flex',
                            })}
                        >
                            <LogIn className="size-3.5" />
                            <span>Iniciar Sesión</span>
                        </Link>
                    )}

                    {/* Botón Reservar */}
                    <Link
                        href="/habitaciones"
                        className={buttonVariants({
                            size: 'sm',
                            className:
                                'hidden items-center gap-1.5 rounded-full px-4 text-xs font-black shadow-xs sm:inline-flex',
                        })}
                    >
                        <CalendarCheck className="size-3.5" />
                        <span>Reservar</span>
                    </Link>

                    {/* Menú Móvil con DropdownMenu */}
                    <div className="md:hidden">
                        <DropdownMenu>
                            <DropdownMenuTrigger
                                className={buttonVariants({
                                    variant: 'outline',
                                    size: 'icon',
                                    className: 'cursor-pointer rounded-full',
                                })}
                                aria-label="Abrir menú"
                            >
                                <Menu className="size-5" />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                className="w-60 p-2 font-sans"
                            >
                                {usuario && (
                                    <>
                                        <DropdownMenuLabel className="rounded-lg bg-muted/40 p-2.5">
                                            <div className="flex items-center gap-2.5">
                                                <div className="flex size-7 items-center justify-center rounded-full bg-gradient-to-tr from-primary to-rose-400 text-[10px] font-black text-white">
                                                    {iniciales}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-xs font-bold text-foreground">
                                                        {usuario.name ||
                                                            'Huésped'}
                                                    </p>
                                                    <p className="truncate text-[10px] text-muted-foreground">
                                                        {usuario.email}
                                                    </p>
                                                </div>
                                            </div>
                                        </DropdownMenuLabel>
                                        <DropdownMenuSeparator className="my-1.5" />
                                    </>
                                )}

                                {enlaces.map((item) => (
                                    <DropdownMenuItem
                                        key={item.nombre}
                                        onClick={() => router.visit(item.href)}
                                        className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-foreground"
                                    >
                                        {item.nombre}
                                    </DropdownMenuItem>
                                ))}

                                <div className="my-1 border-t border-border/60" />

                                {usuario ? (
                                    <>
                                        {usuario.is_admin && (
                                            <DropdownMenuItem
                                                onClick={() =>
                                                    router.visit('/admin')
                                                }
                                                className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-primary dark:text-rose-400"
                                            >
                                                <LayoutDashboard className="size-3.5" />
                                                <span>Panel Admin</span>
                                            </DropdownMenuItem>
                                        )}

                                        <DropdownMenuItem
                                            onClick={() =>
                                                router.visit('/mis-reservas')
                                            }
                                            className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium text-foreground"
                                        >
                                            <BedDouble className="size-3.5 text-muted-foreground" />
                                            <span>Mis Reservas</span>
                                        </DropdownMenuItem>

                                        <DropdownMenuItem
                                            onClick={() =>
                                                router.visit(
                                                    '/auth/cambiar-contrasena',
                                                )
                                            }
                                            className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium text-foreground"
                                        >
                                            <KeyRound className="size-3.5 text-muted-foreground" />
                                            <span>Cambiar Contraseña</span>
                                        </DropdownMenuItem>

                                        <DropdownMenuSeparator className="my-1.5" />

                                        <DropdownMenuItem
                                            variant="destructive"
                                            onClick={() =>
                                                router.post('/auth/logout')
                                            }
                                            className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-destructive"
                                        >
                                            <LogOut className="size-3.5" />
                                            <span>Cerrar Sesión</span>
                                        </DropdownMenuItem>
                                    </>
                                ) : (
                                    <DropdownMenuItem
                                        onClick={() =>
                                            router.visit('/auth/login')
                                        }
                                        className="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-bold text-foreground"
                                    >
                                        <LogIn className="size-3.5" />
                                        <span>Iniciar Sesión / Registro</span>
                                    </DropdownMenuItem>
                                )}

                                <DropdownMenuItem
                                    onClick={() =>
                                        router.visit('/habitaciones')
                                    }
                                    className="mt-1.5 cursor-pointer rounded-lg bg-primary py-2 text-center text-xs font-black text-primary-foreground focus:bg-primary/90"
                                >
                                    Ver Habitaciones
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>
        </header>
    );
};

export default Header;
