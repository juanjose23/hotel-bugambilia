import { Link } from '@inertiajs/react';
import { Heart, Users, Crown, Hotel, ArrowRight } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/modules/shared/components/ui/carousel';
import type { CarouselApi } from '@/modules/shared/components/ui/carousel';
import type { RoomItem } from '@/modules/shared/types';

export type { RoomItem } from '@/modules/shared/types';

interface PropsRoomCard {
    room: RoomItem;
    noches?: number;
    checkIn?: string;
    checkOut?: string;
    huespedes?: number | string;
}

export const RoomCard = ({
    room,
    noches = 0,
    checkIn = '',
    checkOut = '',
    huespedes = '',
}: PropsRoomCard) => {
    const [esFavorito, setEsFavorito] = useState(false);
    const [api, setApi] = useState<CarouselApi>();
    const [actual, setActual] = useState(0);

    const imagenes =
        room.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : room.imagen
              ? [room.imagen]
              : [];

    const precioNoche = Number(room.precio ?? room.precio_desde ?? 0);
    const moneda = room.moneda || '$';
    const totalEstancia = noches > 0 ? precioNoche * noches : null;

    if (api) {
        api.on('select', () => {
            setActual(api.selectedScrollSnap());
        });
    }

    // Parámetros de URL para continuar la reserva sin perder las fechas
    const queryParams = new URLSearchParams();

    if (checkIn) {
        queryParams.set('check_in', checkIn);
    }

    if (checkOut) {
        queryParams.set('check_out', checkOut);
    }

    if (huespedes && huespedes !== 'todos') {
        queryParams.set('huespedes', String(huespedes));
    }

    const urlDetalle = `/habitaciones/${room.slug}${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;

    return (
        <div className="group relative flex h-full flex-col justify-between rounded-3xl border border-border/70 bg-card p-3 font-sans shadow-sm transition-all duration-300 hover:border-primary/40 hover:shadow-lg">
            {/* Contenedor de Imagen con Relación de Aspecto Fija (4:3 uniforme) */}
            <div>
                <div className="relative aspect-4/3 w-full shrink-0 overflow-hidden rounded-2xl bg-muted shadow-xs">
                    {imagenes.length > 0 ? (
                        <Carousel setApi={setApi} className="h-full w-full">
                            <CarouselContent className="-ml-0 h-full">
                                {imagenes.map((imgUrl, index) => (
                                    <CarouselItem
                                        key={index}
                                        className="h-full pl-0"
                                    >
                                        <Link
                                            href={urlDetalle}
                                            className="block h-full w-full"
                                        >
                                            <img
                                                src={imgUrl}
                                                alt={`${room.nombre} - foto ${index + 1}`}
                                                className="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                                loading={
                                                    index === 0
                                                        ? 'eager'
                                                        : 'lazy'
                                                }
                                            />
                                        </Link>
                                    </CarouselItem>
                                ))}
                            </CarouselContent>

                            {/* Botones de navegación en hover */}
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
                    ) : (
                        <Link
                            href={urlDetalle}
                            className="flex h-full w-full items-center justify-center bg-muted/60"
                        >
                            <Hotel className="size-8 text-muted-foreground/40" />
                        </Link>
                    )}

                    {/* Categoría Badge */}
                    {room.categoria && (
                        <div className="pointer-events-none absolute top-2.5 left-2.5 z-10 flex items-center gap-1 rounded-full border border-white/20 bg-background/90 px-2.5 py-0.5 text-[10px] font-black text-foreground shadow-xs backdrop-blur-md">
                            <Crown className="size-2.5 text-primary dark:text-rose-400" />
                            <span>{room.categoria}</span>
                        </div>
                    )}

                    {/* Botón Favorito */}
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            setEsFavorito(!esFavorito);
                        }}
                        aria-label="Guardar en favoritos"
                        className="absolute top-2.5 right-2.5 z-10 size-7 rounded-full p-0 transition-transform hover:bg-black/20 active:scale-75"
                    >
                        <Heart
                            className={`size-4 transition-colors ${
                                esFavorito
                                    ? 'fill-rose-500 text-rose-500'
                                    : 'fill-black/30 stroke-[2.2] text-white'
                            }`}
                        />
                    </Button>
                </div>

                {/* Bloque de Información */}
                <div className="mt-3 px-1">
                    <div className="flex items-center justify-between">
                        <Link
                            href={urlDetalle}
                            className="truncate text-base font-black text-foreground transition-colors hover:text-primary"
                        >
                            {room.nombre}
                        </Link>
                    </div>

                    <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                        {room.categoria && (
                            <span className="truncate font-medium">
                                {room.categoria}
                            </span>
                        )}
                        {room.categoria && room.capacidad ? (
                            <span>•</span>
                        ) : null}
                        {room.capacidad ? (
                            <span className="flex items-center gap-1">
                                <Users className="size-3 text-muted-foreground/70" />
                                <span>Hasta {room.capacidad} huéspedes</span>
                            </span>
                        ) : null}
                    </div>
                </div>
            </div>

            {/* Precios & CTA claro */}
            <div className="mt-4 border-t border-border/60 px-1 pt-3">
                <div className="flex items-baseline justify-between">
                    <div>
                        <div className="flex items-baseline gap-1">
                            <span className="text-base font-black text-foreground">
                                {moneda}
                                {precioNoche.toLocaleString()}
                            </span>
                            <span className="text-[11px] font-medium text-muted-foreground">
                                / noche
                            </span>
                        </div>

                        {/* Precio total de la estancia si hay fechas seleccionadas */}
                        {totalEstancia !== null && (
                            <div className="mt-0.5 text-xs font-bold text-primary dark:text-rose-400">
                                {moneda}
                                {totalEstancia.toLocaleString()} total ·{' '}
                                {noches} {noches === 1 ? 'noche' : 'noches'}
                            </div>
                        )}
                    </div>

                    <Link
                        href={urlDetalle}
                        className="inline-flex items-center gap-1 rounded-xl bg-primary/10 px-3 py-1.5 text-xs font-black text-primary transition-all hover:bg-primary hover:text-primary-foreground dark:bg-rose-950/50 dark:text-rose-300"
                    >
                        <span>{noches > 0 ? 'Seleccionar' : 'Ver suite'}</span>
                        <ArrowRight className="size-3.5" />
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default RoomCard;
