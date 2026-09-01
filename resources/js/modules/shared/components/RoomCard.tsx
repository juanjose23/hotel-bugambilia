import { Link } from '@inertiajs/react';
import { Heart, Users, Crown, Hotel } from 'lucide-react';
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
}

export const RoomCard = ({ room }: PropsRoomCard) => {
    const [esFavorito, setEsFavorito] = useState(false);
    const [api, setApi] = useState<CarouselApi>();
    const [actual, setActual] = useState(0);

    const imagenes =
        room.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : room.imagen
              ? [room.imagen]
              : [];

    const precio = Number(room.precio ?? room.precio_desde ?? 0);
    const moneda = room.moneda || '$';

    if (api) {
        api.on('select', () => {
            setActual(api.selectedScrollSnap());
        });
    }

    return (
        <div className="group relative flex h-full flex-col font-sans">
            {/* Contenedor de Imagen con Relación de Aspecto Fija (4:3 uniforme) */}
            <div className="relative aspect-4/3 w-full shrink-0 overflow-hidden rounded-2xl bg-muted shadow-xs transition-all duration-300">
                {imagenes.length > 0 ? (
                    <Carousel setApi={setApi} className="h-full w-full">
                        <CarouselContent className="-ml-0 h-full">
                            {imagenes.map((imgUrl, index) => (
                                <CarouselItem
                                    key={index}
                                    className="h-full pl-0"
                                >
                                    <Link
                                        href={`/habitaciones/${room.slug}`}
                                        className="block h-full w-full"
                                    >
                                        <img
                                            src={imgUrl}
                                            alt={`${room.nombre} - foto ${index + 1}`}
                                            className="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                            loading={
                                                index === 0 ? 'eager' : 'lazy'
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
                        href={`/habitaciones/${room.slug}`}
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

            {/* Bloque de Información de Altura Uniforme */}
            <Link
                href={`/habitaciones/${room.slug}`}
                className="mt-3 flex flex-1 flex-col justify-between gap-1"
            >
                <div>
                    <div className="flex items-center justify-between text-sm">
                        <span className="truncate font-bold text-foreground">
                            {room.nombre}
                        </span>
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
                                <span>Hasta {room.capacidad} personas</span>
                            </span>
                        ) : null}
                    </div>
                </div>

                <div className="mt-1.5 flex items-baseline gap-1 text-xs">
                    <span className="text-sm font-extrabold text-foreground">
                        {moneda}
                        {precio.toFixed(0)}
                    </span>
                    <span className="text-[11px] text-muted-foreground">
                        / noche
                    </span>
                </div>
            </Link>
        </div>
    );
};

export default RoomCard;
