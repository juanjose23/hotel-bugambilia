import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Users,
    MapPin,
    Calendar,
    Phone,
    MessageSquare,
    Tag,
} from 'lucide-react';
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
import type { EspacioItem } from '../types';

interface PropsEspacioDetalleHero {
    space: EspacioItem;
    alAbrirCotizacion: () => void;
    telefonoWhatsApp?: string;
}

export const EspacioDetalleHero = ({
    space,
    alAbrirCotizacion,
    telefonoWhatsApp = '50587136805',
}: PropsEspacioDetalleHero) => {
    const [api, setApi] = useState<CarouselApi>();
    const [actual, setActual] = useState(0);

    const imagenes =
        space.imagenes && space.imagenes.length > 0
            ? space.imagenes
            : ['/images/service-events.webp'];

    const telefonoLimpio = telefonoWhatsApp.replace(/\D/g, '');

    const mensajeWhatsApp = encodeURIComponent(
        `¡Hola Hotel Bugambilias! 👋\n\n` +
            `Deseo consultar disponibilidad y cotización para el espacio:\n` +
            `🏛️ *${space.nombre}* (${space.tipo_label || space.tipo})\n` +
            (space.precio
                ? `💵 *Tarifa estimada:* ${space.moneda || 'C$'}${Number(space.precio).toFixed(0)} ${space.tipo_tarifa_label || ''}\n`
                : '') +
            `👥 *Capacidad requerida:* ${space.capacidad || 50} personas\n\n` +
            `¿Podrían brindarme información para reservar las fechas?`,
    );

    const enlaceReservar = `/espacios/${space.slug || space.id}/reservar`;

    if (api) {
        api.on('select', () => {
            setActual(api.selectedScrollSnap());
        });
    }

    return (
        <div className="font-sans">
            {/* Barra de Retorno */}
            <div className="border-b border-border/60 bg-card/40 py-3.5 backdrop-blur-md">
                <div className="container mx-auto flex items-center justify-between px-4 sm:px-6">
                    <Link
                        href="/espacios"
                        className="inline-flex items-center gap-1.5 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        <span>Volver al catálogo de espacios</span>
                    </Link>

                    {space.tipo_label && (
                        <span className="rounded-full border border-border bg-muted px-3 py-0.5 text-xs font-bold text-foreground">
                            {space.tipo_label}
                        </span>
                    )}
                </div>
            </div>

            <div className="container mx-auto px-4 py-8 sm:px-6 lg:max-w-5xl">
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Galería Fotográfica con Carousel shadcn (7 cols) */}
                    <div className="flex flex-col gap-3 lg:col-span-7">
                        <div className="relative aspect-4/3 w-full overflow-hidden rounded-3xl border border-border bg-muted shadow-lg">
                            <Carousel setApi={setApi} className="h-full w-full">
                                <CarouselContent className="-ml-0 h-full">
                                    {imagenes.map((imgUrl, idx) => (
                                        <CarouselItem
                                            key={idx}
                                            className="h-full pl-0"
                                        >
                                            <img
                                                src={imgUrl}
                                                alt={`${space.nombre} - foto ${idx + 1}`}
                                                className="h-full w-full object-cover"
                                            />
                                        </CarouselItem>
                                    ))}
                                </CarouselContent>

                                {imagenes.length > 1 && (
                                    <>
                                        <CarouselPrevious className="left-3 size-8 bg-background/80 shadow-md hover:bg-background" />
                                        <CarouselNext className="right-3 size-8 bg-background/80 shadow-md hover:bg-background" />
                                    </>
                                )}
                            </Carousel>

                            {/* Tarifa de BD si aplica */}
                            {space.precio !== undefined && space.precio > 0 && (
                                <div className="absolute right-4 bottom-4 z-10 flex items-center gap-1.5 rounded-full border border-white/20 bg-foreground/95 px-3.5 py-1 text-sm font-black text-background shadow-lg backdrop-blur-md">
                                    <Tag className="size-4" />
                                    <span>
                                        {space.moneda || 'C$'}
                                        {Number(space.precio).toFixed(0)}{' '}
                                        <span className="text-[11px] font-normal text-background/80">
                                            {space.tipo_tarifa_label}
                                        </span>
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Miniaturas interactivas */}
                        {imagenes.length > 1 && (
                            <div className="flex scrollbar-none items-center gap-2 overflow-x-auto pb-1">
                                {imagenes.map((img, idx) => (
                                    <Button
                                        key={idx}
                                        type="button"
                                        variant="ghost"
                                        onClick={() => api?.scrollTo(idx)}
                                        className={`relative aspect-4/3 h-16 shrink-0 overflow-hidden rounded-xl border p-0 transition-all ${
                                            actual === idx
                                                ? 'scale-105 border-primary shadow-sm ring-2 ring-primary/40'
                                                : 'border-border opacity-70 hover:opacity-100'
                                        }`}
                                    >
                                        <img
                                            src={img}
                                            alt={`${space.nombre} ${idx + 1}`}
                                            className="h-full w-full object-cover"
                                        />
                                    </Button>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Información & Acciones (5 cols) */}
                    <div className="flex flex-col justify-between lg:col-span-5">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-bold text-muted-foreground">
                                <MapPin className="size-3.5 text-primary dark:text-rose-400" />
                                <span>
                                    {space.ubicacion ||
                                        'Instalaciones Principales'}
                                </span>
                            </div>

                            <h1 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                                {space.nombre}
                            </h1>

                            {/* Badges de Capacidad */}
                            <div className="mt-3 flex flex-wrap gap-2">
                                {space.capacidad !== undefined &&
                                    space.capacidad > 0 && (
                                        <span className="inline-flex items-center gap-1 rounded-full border border-border bg-muted px-3 py-1 text-xs font-bold text-foreground">
                                            <Users className="size-3 text-primary dark:text-rose-400" />
                                            <span>
                                                Capacidad: {space.capacidad}{' '}
                                                personas
                                            </span>
                                        </span>
                                    )}
                            </div>

                            <p className="mt-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                {space.descripcion}
                            </p>
                        </div>

                        {/* Botones de Acción */}
                        <div className="mt-8 flex flex-col gap-3 border-t border-border/60 pt-6">
                            {space.reservable && (
                                <Link
                                    href={enlaceReservar}
                                    className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary py-3 text-xs font-black text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95"
                                >
                                    <Calendar className="size-3.5" />
                                    <span>Reservar Espacio Online</span>
                                </Link>
                            )}

                            <a
                                href={`https://wa.me/${telefonoLimpio}?text=${mensajeWhatsApp}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95 dark:bg-emerald-700 dark:hover:bg-emerald-800"
                            >
                                <Phone className="size-3.5" />
                                <span>Consultar por WhatsApp</span>
                            </a>

                            <Button
                                type="button"
                                variant="outline"
                                onClick={alAbrirCotizacion}
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border-border py-3 text-xs font-bold shadow-xs transition-all hover:bg-muted active:scale-95"
                            >
                                <MessageSquare className="size-3.5" />
                                <span>Solicitar Cotización de Evento</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EspacioDetalleHero;
