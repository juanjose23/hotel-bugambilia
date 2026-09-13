import { Link, usePage } from '@inertiajs/react';
import { CalendarCheck, Moon, Sun } from 'lucide-react';
import { useMemo } from 'react';
import { Button, buttonVariants } from '@/modules/shared/components/ui/button';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
import { useTema } from '@/modules/shared/hooks/useTema';
import { HeaderMenuMovil } from './HeaderMenuMovil';
import { HeaderMenuUsuario } from './HeaderMenuUsuario';
import { HeaderNavegacion } from './HeaderNavegacion';
import type { EnlaceNavegacion } from './HeaderNavegacion';

export const Header = () => {
    const ruta = usePage().url;
    const { tema, alternarTema } = useTema();
    const { auth, restaurante_activo } = usePropiedadesPagina();
    const usuario = auth?.user;

    const listaEnlaces = useMemo<EnlaceNavegacion[]>(() => {
        const base: EnlaceNavegacion[] = [
            { nombre: 'Inicio', href: '/' },
            { nombre: 'Habitaciones', href: '/habitaciones' },
            { nombre: 'Promociones', href: '/promociones' },
            { nombre: 'Espacios', href: '/espacios' },
        ];

        if (restaurante_activo) {
            base.push({ nombre: 'Restaurante', href: '/restaurante' });
        }

        base.push(
            { nombre: 'Servicios', href: '/servicios' },
            { nombre: 'Nosotros', href: '/acerca-de' },
            { nombre: 'Contacto', href: '/contacto' },
        );

        return base;
    }, [restaurante_activo]);

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

                <HeaderNavegacion listaEnlaces={listaEnlaces} ruta={ruta} />

                <div className="flex items-center gap-2.5">
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

                    <HeaderMenuUsuario
                        usuario={usuario}
                        iniciales={iniciales}
                    />

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

                    <HeaderMenuMovil
                        usuario={usuario}
                        iniciales={iniciales}
                        listaEnlaces={listaEnlaces}
                    />
                </div>
            </div>
        </header>
    );
};

export default Header;
