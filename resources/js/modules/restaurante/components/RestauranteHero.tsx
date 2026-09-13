import { Link } from '@inertiajs/react';
import {
    UtensilsCrossed,
    PhoneCall,
    Users,
    Truck,
    ChefHat,
    ShoppingBag,
    CalendarCheck,
} from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';
import type { RestauranteData } from '../types';

interface PropsRestauranteHero {
    restaurante: RestauranteData;
}

export const RestauranteHero = ({ restaurante }: PropsRestauranteHero) => {
    const imagenHero =
        restaurante.imagenes && restaurante.imagenes.length > 0
            ? restaurante.imagenes[0]
            : '/images/service-events.webp';

    const scrollAlMenu = (e: React.MouseEvent) => {
        e.preventDefault();
        const elemento = document.getElementById('menu-restaurante');

        if (elemento) {
            elemento.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <section
            aria-label={`Portada de ${restaurante.nombre}`}
            className="relative overflow-hidden border-b border-border py-16 font-sans text-white sm:py-24 lg:py-28"
        >
            {/* Banner con Fotografía Real de Hotel Bugambilias */}
            <div className="absolute inset-0 z-0">
                <img
                    src={imagenHero}
                    alt={`Ambiente real de ${restaurante.nombre} en Hotel Bugambilias`}
                    className="h-full w-full object-cover object-center brightness-[0.70] contrast-[1.05] dark:brightness-[0.45]"
                    loading="eager"
                />
                <div
                    aria-hidden="true"
                    className="absolute inset-0 bg-gradient-to-t from-background via-black/45 to-black/75"
                />
            </div>

            {/* Contenido Central */}
            <div className="relative z-10 container mx-auto flex flex-col items-center justify-center px-4 text-center text-white sm:px-6">
                {/* Badge Institucional */}
                <div className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-black tracking-wider text-rose-200 uppercase shadow-xs backdrop-blur-md">
                    <UtensilsCrossed className="size-3.5 text-rose-300" />
                    <span>Hotel Bugambilias Estelí • Restaurante & Bar</span>
                </div>

                {/* Título Principal */}
                <h1 className="mt-4 max-w-3xl text-3xl font-black tracking-tight text-white drop-shadow-md sm:text-5xl lg:text-6xl">
                    {restaurante.nombre}
                </h1>

                {/* Descripción */}
                <p className="mt-3 max-w-xl text-xs leading-relaxed font-medium text-zinc-100 drop-shadow-xs sm:text-sm">
                    {restaurante.descripcion}
                </p>

                {/* Micro-puntos de valor */}
                <div className="mt-5 flex flex-wrap items-center justify-center gap-2.5 text-xs font-bold text-white/95 sm:gap-4">
                    <div className="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/45 px-3.5 py-1 backdrop-blur-xs">
                        <Users className="size-3.5 text-rose-300" />
                        <span>Capacidad {restaurante.capacidad} personas</span>
                    </div>
                    <div className="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/45 px-3.5 py-1 backdrop-blur-xs">
                        <ChefHat className="size-3.5 text-amber-300" />
                        <span>{restaurante.tipo_cocina}</span>
                    </div>
                    {restaurante.permite_delivery && (
                        <div className="flex items-center gap-1.5 rounded-full border border-emerald-500/40 bg-emerald-950/70 px-3.5 py-1 text-emerald-200 backdrop-blur-xs">
                            <Truck className="size-3.5 text-emerald-400" />
                            <span>Delivery en Estelí</span>
                        </div>
                    )}
                </div>

                {/* Botones de Acción */}
                <div className="mt-8 flex flex-wrap items-center justify-center gap-3.5">
                    {restaurante.permite_delivery ? (
                        <a
                            href="#menu-restaurante"
                            onClick={scrollAlMenu}
                            className={buttonVariants({
                                size: 'default',
                                className:
                                    'cursor-pointer gap-2 rounded-full bg-emerald-600 px-6 text-xs font-black text-white shadow-xl transition-all hover:bg-emerald-500 active:scale-95 sm:text-sm',
                            })}
                        >
                            <ShoppingBag className="size-4" />
                            <span>Hacer Pedido (Delivery)</span>
                        </a>
                    ) : null}

                    <Link
                        href="/restaurante/reservar"
                        className={buttonVariants({
                            size: 'default',
                            className:
                                'cursor-pointer gap-2 rounded-full bg-primary px-6 text-xs font-bold text-primary-foreground shadow-xl transition-all hover:bg-primary/90 active:scale-95 sm:text-sm',
                        })}
                    >
                        <CalendarCheck className="size-4" />
                        <span>Reservar Mesa (Plano)</span>
                    </Link>

                    <a
                        href="#menu-restaurante"
                        onClick={scrollAlMenu}
                        className={buttonVariants({
                            variant: 'outline',
                            size: 'default',
                            className:
                                'cursor-pointer gap-2 rounded-full border-white/20 bg-white/10 px-5 text-xs font-bold text-white backdrop-blur-xs transition-all hover:bg-white/20 active:scale-95 sm:text-sm',
                        })}
                    >
                        <UtensilsCrossed className="size-4 text-rose-300" />
                        <span>Ver Menú</span>
                    </a>

                    <Link
                        href="/contacto"
                        className={buttonVariants({
                            variant: 'ghost',
                            size: 'default',
                            className:
                                'cursor-pointer gap-2 rounded-full border border-white/15 bg-black/30 px-4 text-xs font-semibold text-white/90 transition-all hover:bg-black/50 hover:text-white active:scale-95 sm:text-sm',
                        })}
                    >
                        <PhoneCall className="size-3.5 text-amber-300" />
                        <span>Recepción</span>
                    </Link>
                </div>
            </div>
        </section>
    );
};

export default RestauranteHero;
