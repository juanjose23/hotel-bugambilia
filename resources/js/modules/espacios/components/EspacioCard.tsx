import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Users,
    MapPin,
    Calendar,
    UtensilsCrossed,
    Activity,
    Waves,
    Building2,
} from 'lucide-react';
import { useState } from 'react';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/modules/shared/components/ui/carousel';
import type { CarouselApi } from '@/modules/shared/components/ui/carousel';
import type { EspacioItem } from '../types';

interface PropsEspacioCard {
    espacio: EspacioItem;
}

const renderIconoEspacio = (tipo?: string) => {
    switch (tipo?.toLowerCase()) {
        case 'restaurante':
            return (
                <UtensilsCrossed className="size-3 text-primary dark:text-rose-400" />
            );
        case 'gym':
            return (
                <Activity className="size-3 text-primary dark:text-rose-400" />
            );
        case 'salon':
        case 'eventos':
            return <Users className="size-3 text-primary dark:text-rose-400" />;
        case 'piscina':
            return <Waves className="size-3 text-primary dark:text-rose-400" />;
        default:
            return (
                <Building2 className="size-3 text-primary dark:text-rose-400" />
            );
    }
};

export const EspacioCard = ({ espacio }: PropsEspacioCard) => {
    const [api, setApi] = useState<CarouselApi>();
    const [actual, setActual] = useState(0);

    const imagenes =
        espacio.imagenes && espacio.imagenes.length > 0
            ? espacio.imagenes
            : ['/images/service-events.webp'];

    const enlaceDetalle = espacio.es_restaurante
        ? '/restaurante'
        : `/espacios/${espacio.slug || espacio.id}`;

    const enlaceReservar = `/espacios/${espacio.slug || espacio.id}/reservar`;

    if (api) {
        api.on('select', () => {
            setActual(api.selectedScrollSnap());
        });
    }

    return (
        <div className="group flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl dark:hover:border-rose-500/50">
            {/* Contenedor de Imagen con Carousel oficial shadcn */}
            <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                <Carousel setApi={setApi} className="h-full w-full">
                    <CarouselContent className="-ml-0 h-full">
                        {imagenes.map((imgUrl, index) => (
                            <CarouselItem key={index} className="h-full pl-0">
                                <Link
                                    href={enlaceDetalle}
                                    className="block h-full w-full"
                                >
                                    <img
                                        src={imgUrl}
                                        alt={`${espacio.nombre} - foto ${index + 1}`}
                                        className="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                        loading={index === 0 ? 'eager' : 'lazy'}
                                    />
                                </Link>
                            </CarouselItem>
                        ))}
                    </CarouselContent>

                    {/* Botones de navegación en hover si hay múltiples imágenes */}
                    {imagenes.length > 1 && (
                        <>
                            <CarouselPrevious className="left-2 size-7 opacity-0 shadow-md transition-opacity duration-200 group-hover:opacity-90 hover:scale-110 hover:opacity-100" />
                            <CarouselNext className="right-2 size-7 opacity-0 shadow-md transition-opacity duration-200 group-hover:opacity-90 hover:scale-110 hover:opacity-100" />

                            {/* Puntos indicadores */}
                            <div className="pointer-events-none absolute right-0 bottom-2.5 left-0 flex justify-center gap-1">
                                {imagenes.map((_, dotIdx) => (
                                    <span
                                        key={dotIdx}
                                        className={`size-1.5 rounded-full transition-all duration-200 ${
                                            actual === dotIdx
                                                ? 'w-3 bg-white shadow-xs'
                                                : 'bg-white/60'
                                        }`}
                                    />
                                ))}
                            </div>
                        </>
                    )}
                </Carousel>

                {/* Badge de Categoría / Tipo */}
                <span className="pointer-events-none absolute top-3.5 left-3.5 z-10 flex items-center gap-1 rounded-full border border-white/20 bg-background/90 px-3 py-0.5 text-[11px] font-black text-foreground shadow-xs backdrop-blur-md">
                    {renderIconoEspacio(espacio.tipo)}
                    <span>{espacio.tipo_label || espacio.tipo}</span>
                </span>

                {/* Badge de Capacidad */}
                {espacio.capacidad !== undefined && espacio.capacidad > 0 && (
                    <span className="pointer-events-none absolute right-3.5 bottom-3.5 z-10 flex items-center gap-1 rounded-full border border-white/20 bg-black/80 px-2.5 py-0.5 text-xs font-black text-white shadow-xs backdrop-blur-md">
                        <Users className="size-3 text-rose-300" />
                        <span>Hasta {espacio.capacidad} personas</span>
                    </span>
                )}
            </div>

            {/* Contenido */}
            <div className="flex flex-1 flex-col justify-between p-6">
                <div>
                    <div className="flex items-center gap-1.5 text-xs font-bold text-muted-foreground">
                        <MapPin className="size-3 text-primary dark:text-rose-400" />
                        <span>
                            {espacio.ubicacion || 'Hotel Bugambilias Estelí'}
                        </span>
                    </div>

                    <h3 className="mt-1.5 text-lg font-black tracking-tight text-foreground transition-colors group-hover:text-primary sm:text-xl dark:group-hover:text-rose-400">
                        {espacio.nombre}
                    </h3>

                    <p className="mt-2 line-clamp-2 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                        {espacio.descripcion}
                    </p>
                </div>

                <div className="mt-6 flex items-center justify-between border-t border-border/60 pt-4">
                    <Link
                        href={enlaceDetalle}
                        className="inline-flex items-center gap-1 text-xs font-black text-primary hover:underline dark:text-rose-400"
                    >
                        <span>Conocer espacio</span>
                        <ArrowRight className="size-3.5 transition-transform group-hover:translate-x-1" />
                    </Link>

                    {espacio.reservable && (
                        <Link
                            href={enlaceReservar}
                            className="inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-primary px-4 py-1.5 text-xs font-bold text-primary-foreground shadow-xs transition-transform hover:bg-primary/90 active:scale-95"
                        >
                            <Calendar className="size-3" />
                            <span>Reservar</span>
                        </Link>
                    )}
                </div>
            </div>
        </div>
    );
};

export default EspacioCard;
