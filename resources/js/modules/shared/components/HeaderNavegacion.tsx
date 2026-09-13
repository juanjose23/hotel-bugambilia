import { Link } from '@inertiajs/react';
import { buttonVariants } from '@/modules/shared/components/ui/button';

export interface EnlaceNavegacion {
    nombre: string;
    href: string;
}

interface HeaderNavegacionProps {
    listaEnlaces: EnlaceNavegacion[];
    ruta: string;
}

export const HeaderNavegacion = ({
    listaEnlaces,
    ruta,
}: HeaderNavegacionProps) => {
    return (
        <nav className="hidden items-center gap-1 rounded-full border border-border/80 bg-card/80 px-3 py-1 shadow-xs md:flex">
            {listaEnlaces.map((item) => {
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
                            className: 'rounded-full px-4 text-xs font-bold',
                        })}
                    >
                        {item.nombre}
                    </Link>
                );
            })}
        </nav>
    );
};

export default HeaderNavegacion;
