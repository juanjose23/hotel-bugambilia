import { Link } from '@inertiajs/react';
import {
    ShieldCheck,
    Users,
    BedDouble,
    ChevronLeft,
    Sparkles,
    Maximize2,
    CheckCircle2,
    Maximize,
    Eye,
    AlertCircle,
    Box,
} from 'lucide-react';
import { useState } from 'react';
import BotonReservaWhatsApp from '@/modules/compartido/componentes/BotonReservaWhatsApp';
import VisorGaleriaModal from '@/modules/compartido/componentes/VisorGaleriaModal';
import type {
    HabitacionItem,
    HabitacionSimilares,
} from '@/modules/compartido/tipos';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface HabitacionDetalleProps {
    room: HabitacionItem & {
        imagenes: string[];
    };
    similarRooms?: HabitacionSimilares[];
}

export default function HabitacionDetalle({
    room,
    similarRooms = [],
}: HabitacionDetalleProps) {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);

    const imagenes =
        room?.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : ['/images/main-room.jpg'];
    const currentImage = imagenes[activeImageIndex] || imagenes[0];

    const serviciosIncluidos = room?.serviciosIncluidos || [];
    const politicas = room?.politicas || [];
    const equipamiento = room?.equipamiento || [];
    const vistas = room?.vistas || [];

    return (
        <LayoutPublico>
            {/* Migas de Pan / Breadcrumbs */}
            <div className="border-b border-border/40 bg-card py-3 font-sans">
                <div className="container mx-auto flex items-center gap-2 px-4 text-xs font-semibold text-muted-foreground sm:px-6 lg:px-8">
                    <Link
                        href="/habitaciones"
                        className="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                    >
                        <ChevronLeft className="h-3.5 w-3.5" />
                        Habitaciones
                    </Link>
                    <span>/</span>
                    <span className="font-bold text-foreground">
                        {room?.categoria || 'Habitación'}
                    </span>
                    <span>/</span>
                    <span className="max-w-[220px] truncate font-bold text-bugambilia-600 dark:text-bugambilia-400">
                        {room?.nombre}
                    </span>
                </div>
            </div>

            {/* Hero Principal con Galería */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Fotografía Principal & Miniaturas */}
                        <div className="space-y-4 lg:col-span-7">
                            <div
                                onClick={() => setIsLightboxOpen(true)}
                                className="group shadow-airbnb relative aspect-[16/10] cursor-pointer overflow-hidden rounded-3xl border border-border/80 bg-muted/40"
                            >
                                <img
                                    src={currentImage}
                                    alt={room?.nombre}
                                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />

                                <div className="absolute top-4 left-4 z-10 flex items-center gap-2">
                                    <span className="rounded-full border border-white/20 bg-black/70 px-3.5 py-1.5 text-xs font-extrabold tracking-wider text-white uppercase backdrop-blur-md">
                                        {room?.categoria}
                                    </span>
                                </div>

                                <div className="absolute right-4 bottom-4 z-10">
                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-black/60 px-3 py-1.5 text-xs font-bold text-white opacity-90 backdrop-blur-md transition-opacity group-hover:opacity-100">
                                        <Maximize2 className="h-3.5 w-3.5" />
                                        Ver Pantalla Completa
                                    </span>
                                </div>
                            </div>

                            {/* Miniaturas de Galería */}
                            {imagenes.length > 1 && (
                                <div className="flex items-center gap-3 overflow-x-auto pb-2">
                                    {imagenes.map((img, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() =>
                                                setActiveImageIndex(idx)
                                            }
                                            className={`relative h-20 w-20 shrink-0 cursor-pointer overflow-hidden rounded-2xl border-2 transition-all ${
                                                activeImageIndex === idx
                                                    ? 'scale-95 border-bugambilia-600 shadow-md'
                                                    : 'border-border/60 opacity-70 hover:opacity-100'
                                            }`}
                                        >
                                            <img
                                                src={img}
                                                alt={`Galería ${idx + 1}`}
                                                className="h-full w-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Resumen & Tarjeta de Reservación Directa */}
                        <div className="flex flex-col justify-between lg:col-span-5">
                            <div>
                                <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-extrabold tracking-widest text-amber-500 uppercase">
                                    <Sparkles className="h-3.5 w-3.5" />
                                    Habitación {room?.numero} (Código:{' '}
                                    {room?.codigo})
                                </div>

                                <h1 className="mb-4 text-2xl leading-tight font-black tracking-tight text-foreground sm:text-3xl lg:text-4xl">
                                    {room?.nombre}
                                </h1>

                                {room?.descripcion && (
                                    <p className="mb-6 text-sm leading-relaxed font-medium text-muted-foreground sm:text-base">
                                        {room.descripcion}
                                    </p>
                                )}

                                {/* Grid de Especificaciones */}
                                <div className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div className="flex flex-col justify-center rounded-2xl border border-border/80 bg-card p-3.5">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Capacidad
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <Users className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.capacidad || 2} personas
                                        </span>
                                    </div>

                                    <div className="flex flex-col justify-center rounded-2xl border border-border/80 bg-card p-3.5">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Camas
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <BedDouble className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.camas || '1 Cama King'}
                                        </span>
                                    </div>

                                    <div className="col-span-2 flex flex-col justify-center rounded-2xl border border-border/80 bg-card p-3.5 sm:col-span-1">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Superficie
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <Maximize className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.medidas || '32 m²'}
                                        </span>
                                    </div>
                                </div>

                                {/* Vistas Disponibles */}
                                {vistas.length > 0 && (
                                    <div className="mb-6 flex flex-wrap items-center gap-2">
                                        <span className="text-xs font-bold text-muted-foreground">
                                            Vistas:
                                        </span>
                                        {vistas.map((vista, idx) => (
                                            <span
                                                key={idx}
                                                className="inline-flex items-center gap-1 rounded-full border border-border/80 bg-card px-3 py-1 text-xs font-extrabold text-foreground"
                                            >
                                                <Eye className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                                {vista}
                                            </span>
                                        ))}
                                    </div>
                                )}

                                {/* Caja de Precio */}
                                <div className="shadow-airbnb-subtle mb-6 flex items-center justify-between rounded-2xl border border-border/80 bg-card p-5">
                                    <div>
                                        <span className="mb-0.5 block text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                            Tarifa por Noche
                                        </span>
                                        <div className="text-3xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                            {room?.moneda || '$'}
                                            {room?.precio}{' '}
                                            <span className="text-xs font-semibold text-muted-foreground">
                                                USD
                                            </span>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            <ShieldCheck className="h-4 w-4" />
                                            Disponible
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Botón Reutilizable de WhatsApp */}
                            <BotonReservaWhatsApp
                                nombreItem={room?.nombre || 'Habitación'}
                                codigoItem={room?.codigo}
                                tipo="habitación"
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Servicios Incluidos & Comodidades */}
            {serviciosIncluidos.length > 0 && (
                <section className="border-t border-border/40 bg-background py-12 font-sans md:py-16">
                    <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-6 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                            Servicios Incluidos &{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Comodidades
                            </span>
                        </h2>

                        <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                            {serviciosIncluidos.map((serv, idx) => (
                                <div
                                    key={idx}
                                    className="flex items-center gap-3 rounded-2xl border border-border/80 bg-card p-4"
                                >
                                    <CheckCircle2 className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                    <span className="text-xs leading-snug font-bold text-foreground">
                                        {serv}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Equipamiento Fijo */}
            {equipamiento.length > 0 && (
                <section className="border-t border-border/40 bg-card/40 py-10 font-sans">
                    <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <h3 className="mb-4 flex items-center gap-2 text-lg font-extrabold tracking-tight text-foreground">
                            <Box className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            Equipamiento de la Habitación
                        </h3>

                        <div className="flex flex-wrap gap-2">
                            {equipamiento.map((item, idx) => (
                                <span
                                    key={idx}
                                    className="rounded-full border border-border/80 bg-card px-3.5 py-1.5 text-xs font-semibold text-foreground"
                                >
                                    {item}
                                </span>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Políticas de la Habitación */}
            {politicas.length > 0 && (
                <section className="border-t border-border/40 bg-background py-12 font-sans md:py-16">
                    <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-6 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                            Políticas de la{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Habitación
                            </span>
                        </h2>

                        <div className="grid gap-4 sm:grid-cols-3">
                            {politicas.map((pol) => (
                                <div
                                    key={pol.id || pol.nombre}
                                    className="rounded-2xl border border-border/80 bg-card p-5"
                                >
                                    <div className="mb-2 flex items-center gap-2">
                                        <AlertCircle className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                        <h3 className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            {pol.nombre}
                                        </h3>
                                    </div>
                                    {pol.descripcion && (
                                        <p className="text-xs leading-relaxed text-muted-foreground">
                                            {pol.descripcion}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Visor de Galería Modal Reutilizable */}
            <VisorGaleriaModal
                estaAbierto={isLightboxOpen}
                alCerrar={() => setIsLightboxOpen(false)}
                imagenes={imagenes}
                indiceImagenActiva={activeImageIndex}
                alSeleccionarImagen={(idx) => setActiveImageIndex(idx)}
                titulo={room?.nombre}
            />

            {/* Habitaciones Similares */}
            {similarRooms.length > 0 && (
                <section className="border-t border-border/50 bg-card/60 py-16 font-sans">
                    <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-8 text-2xl font-black tracking-tight text-foreground">
                            Otras Habitaciones{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Disponibles
                            </span>
                        </h2>

                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {similarRooms.map((sim) => (
                                <article
                                    key={sim.id}
                                    className="group shadow-airbnb hover:shadow-airbnb-hover overflow-hidden rounded-3xl border border-border/80 bg-background transition-all duration-300 hover:-translate-y-1"
                                >
                                    <Link
                                        href={`/habitaciones/${sim.slug}`}
                                        className="relative block aspect-[4/3] overflow-hidden bg-muted/40"
                                    >
                                        <img
                                            src={sim.imagen}
                                            alt={sim.nombre}
                                            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />
                                    </Link>
                                    <div className="p-5">
                                        <span className="mb-1 block text-[10px] font-extrabold tracking-wider text-bugambilia-600 uppercase">
                                            {sim.categoria}
                                        </span>
                                        <h3 className="mb-2 text-sm font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600">
                                            {sim.nombre}
                                        </h3>
                                        <div className="flex items-center justify-between border-t border-border/40 pt-3">
                                            <span className="text-base font-black text-foreground">
                                                {sim.moneda}
                                                {sim.precio} USD
                                            </span>
                                            <Link
                                                href={`/habitaciones/${sim.slug}`}
                                                className="text-xs font-bold text-bugambilia-600 hover:underline"
                                            >
                                                Ver Detalles →
                                            </Link>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </LayoutPublico>
    );
}
