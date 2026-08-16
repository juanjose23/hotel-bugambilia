import { Link } from '@inertiajs/react';
import { Share, Heart, Maximize } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { HabitacionReservable } from '../interfaces/reservaHabitacion';

interface PropiedadesPortadaDetalleHabitacion {
    room: HabitacionReservable;
}

export const PortadaDetalleHabitacion = ({
    room,
}: PropiedadesPortadaDetalleHabitacion) => {
    const imagenes =
        room.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : ['/images/main-room.webp'];

    return (
        <section className="bg-background font-sans">
            <div className="container mx-auto px-4 pt-6 pb-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between gap-4">
                    <div className="no-scrollbar flex items-center gap-2 overflow-x-auto py-1">
                        <Link
                            href="/"
                            prefetch
                            className="text-xs font-bold text-muted-foreground hover:text-foreground"
                        >
                            Inicio
                        </Link>
                        <span className="text-muted-foreground/60">/</span>
                        <Link
                            href="/habitaciones"
                            prefetch
                            className="text-xs font-bold text-muted-foreground hover:text-foreground"
                        >
                            Habitaciones
                        </Link>
                        <span className="text-muted-foreground/60">/</span>
                        <span className="truncate text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                            {room.nombre}
                        </span>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            className="gap-2 rounded-2xl text-xs font-bold"
                        >
                            <Share className="size-3.5" />
                            <span className="hidden sm:inline">Compartir</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            className="gap-2 rounded-2xl text-xs font-bold"
                        >
                            <Heart className="size-3.5" />
                            <span className="hidden sm:inline">Guardar</span>
                        </Button>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 pb-8 sm:px-6 lg:px-8">
                <div className="group relative grid h-[360px] grid-cols-4 grid-rows-2 gap-2 overflow-hidden rounded-3xl md:h-[480px] lg:h-[560px]">
                    <div className="relative col-span-4 row-span-2 overflow-hidden lg:col-span-2">
                        <img
                            src={imagenes[0]}
                            alt={room.nombre}
                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                    </div>

                    {imagenes.slice(1, 5).map((image, idx) => (
                        <div
                            key={idx}
                            className="relative hidden overflow-hidden lg:block"
                        >
                            <img
                                src={image}
                                alt={`${room.nombre} vista ${idx + 2}`}
                                className="absolute inset-0 h-full w-full object-cover transition-transform duration-500 hover:scale-110"
                            />
                        </div>
                    ))}

                    <Badge
                        variant="secondary"
                        className="absolute right-6 bottom-6 z-10 flex items-center gap-2 rounded-2xl bg-card/90 px-4 py-2 text-xs font-extrabold text-foreground shadow-lg backdrop-blur-md"
                    >
                        <Maximize className="size-3.5" />
                        <span>Ver todas las fotos</span>
                    </Badge>
                </div>
            </div>
        </section>
    );
};

export default PortadaDetalleHabitacion;
