import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Waves,
    UtensilsCrossed,
    Users,
    Activity,
    Flower2,
    Building2,
    Coffee,
    ConciergeBell,
    CheckCircle2,
} from 'lucide-react';
import type { EspacioHomeItem, ServicioHomeItem } from '../types';

export interface ServicioItem extends ServicioHomeItem {
    precio?: number | null;
    moneda?: string;
}

export interface EspacioItem extends EspacioHomeItem {
    tipo?: string;
    capacidad_personas?: number;
    enlace?: string;
}

interface PropsServicesSection {
    espacios?: EspacioItem[];
    servicios?: ServicioItem[];
}

const resolverIconoPorTexto = (texto?: string) => {
    const t = (texto || '').toLowerCase();

    if (
        t.includes('restaurante') ||
        t.includes('comida') ||
        t.includes('gastronom')
    ) {
        return UtensilsCrossed;
    }

    if (t.includes('piscina') || t.includes('acu') || t.includes('sol')) {
        return Waves;
    }

    if (t.includes('caf') || t.includes('desayun')) {
        return Coffee;
    }

    if (t.includes('spa') || t.includes('masaje') || t.includes('relax')) {
        return Flower2;
    }

    if (t.includes('gym') || t.includes('fitness')) {
        return Activity;
    }

    if (t.includes('salon') || t.includes('evento') || t.includes('reunion')) {
        return Users;
    }

    return ConciergeBell;
};

export const ServicesSection = ({
    espacios = [],
    servicios = [],
}: PropsServicesSection) => {
    const tieneEspacios = espacios.length > 0;
    const tieneServicios = servicios.length > 0;

    return (
        <section
            aria-labelledby="titulo-experiencias-home"
            className="border-t border-border bg-card/40 py-12 font-sans backdrop-blur-md md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                {/* 1. SECCIÓN DE ESPACIOS E INSTALACIONES (Desde la BD) */}
                <div>
                    <div className="flex flex-col items-start justify-between gap-3 md:flex-row md:items-end">
                        <div>
                            <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-wider text-primary uppercase dark:text-rose-400">
                                <Building2
                                    className="size-3.5"
                                    aria-hidden="true"
                                />
                                <span>Instalaciones del Hotel</span>
                            </div>
                            <h2
                                id="titulo-experiencias-home"
                                className="mt-1 text-xl font-black tracking-tight text-foreground sm:text-3xl"
                            >
                                Espacios Destacados en Estelí
                            </h2>
                            <p className="mt-1 text-xs text-muted-foreground sm:text-sm">
                                Áreas climatizadas, jardines y salones listos
                                para tu estancia o evento social.
                            </p>
                        </div>

                        <Link
                            href="/espacios"
                            className="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline dark:text-rose-400"
                        >
                            <span>Ver todos los espacios</span>
                            <ArrowRight
                                className="size-3.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>

                    {/* Grid de Espacios (BD) */}
                    {tieneEspacios ? (
                        <div className="-mx-4 mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4">
                            {espacios.map((espacio) => {
                                const IconoComponente = resolverIconoPorTexto(
                                    espacio.tipo || espacio.nombre,
                                );
                                const enlaceDetalle = espacio.slug
                                    ? `/espacios/${espacio.slug}`
                                    : '/espacios';

                                return (
                                    <Link
                                        key={espacio.id}
                                        href={enlaceDetalle}
                                        className="group flex w-[260px] shrink-0 snap-center flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg sm:w-auto dark:hover:border-rose-500/50"
                                    >
                                        <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                                            {espacio.imagen ? (
                                                <img
                                                    src={espacio.imagen}
                                                    alt={espacio.nombre}
                                                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center bg-primary/5 text-primary/40">
                                                    <IconoComponente className="size-10" />
                                                </div>
                                            )}
                                            {espacio.categoria && (
                                                <span className="absolute top-2.5 left-2.5 rounded-full border border-border/80 bg-background/90 px-2.5 py-0.5 text-[10px] font-black text-foreground shadow-xs backdrop-blur-md">
                                                    {espacio.categoria}
                                                </span>
                                            )}
                                        </div>

                                        <div className="flex flex-1 flex-col justify-between p-4.5">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                                                        <IconoComponente
                                                            className="size-4"
                                                            aria-hidden="true"
                                                        />
                                                    </div>
                                                    <h3 className="truncate text-xs font-bold text-foreground sm:text-sm">
                                                        {espacio.nombre}
                                                    </h3>
                                                </div>
                                                <p className="mt-2 line-clamp-2 text-[11px] leading-relaxed text-muted-foreground sm:text-xs">
                                                    {espacio.descripcion}
                                                </p>
                                            </div>

                                            <div className="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-[11px] font-bold text-muted-foreground">
                                                <span>
                                                    {espacio.capacidad_personas
                                                        ? `Hasta ${espacio.capacidad_personas} personas`
                                                        : 'Espacio disponible'}
                                                </span>
                                                <span className="text-primary group-hover:underline dark:text-rose-400">
                                                    Ver detalles →
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="mt-6 rounded-2xl border border-dashed border-border bg-card/30 p-8 text-center text-xs text-muted-foreground">
                            Cargando espacios disponibles de Hotel
                            Bugambilias...
                        </div>
                    )}
                </div>

                {/* 2. SECCIÓN DE SERVICIOS Y AMENIDADES VIP (Desde la BD) */}
                {tieneServicios && (
                    <div className="mt-14 border-t border-border/70 pt-12">
                        <div className="flex flex-col items-start justify-between gap-3 md:flex-row md:items-end">
                            <div>
                                <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-wider text-primary uppercase dark:text-rose-400">
                                    <ConciergeBell
                                        className="size-3.5"
                                        aria-hidden="true"
                                    />
                                    <span>Servicios & Amenidades</span>
                                </div>
                                <h3 className="mt-1 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                                    Experiencias para tu Estancia
                                </h3>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Servicios exclusivos para hacer tu visita a
                                    Estelí cómoda e inolvidable.
                                </p>
                            </div>

                            <Link
                                href="/servicios"
                                className="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline dark:text-rose-400"
                            >
                                <span>Ver catálogo de servicios</span>
                                <ArrowRight
                                    className="size-3.5"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>

                        {/* Grid de Servicios (BD) */}
                        <div className="-mx-4 mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4">
                            {servicios.map((servicio) => {
                                const IconoServicio = resolverIconoPorTexto(
                                    servicio.categoria || servicio.nombre,
                                );
                                const enlaceServicio = servicio.slug
                                    ? `/servicios/${servicio.slug}`
                                    : '/servicios';

                                return (
                                    <Link
                                        key={servicio.id}
                                        href={enlaceServicio}
                                        className="group flex w-[260px] shrink-0 snap-center flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg sm:w-auto dark:hover:border-rose-500/50"
                                    >
                                        <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                                            {servicio.imagen ? (
                                                <img
                                                    src={servicio.imagen}
                                                    alt={servicio.nombre}
                                                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center bg-primary/5 text-primary/40">
                                                    <IconoServicio className="size-10" />
                                                </div>
                                            )}
                                            {servicio.categoria && (
                                                <span className="absolute top-2.5 left-2.5 rounded-full border border-border/80 bg-background/90 px-2.5 py-0.5 text-[10px] font-black text-foreground shadow-xs backdrop-blur-md">
                                                    {servicio.categoria}
                                                </span>
                                            )}
                                        </div>

                                        <div className="flex flex-1 flex-col justify-between p-4.5">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                                                        <IconoServicio
                                                            className="size-4"
                                                            aria-hidden="true"
                                                        />
                                                    </div>
                                                    <h4 className="truncate text-xs font-bold text-foreground sm:text-sm">
                                                        {servicio.nombre}
                                                    </h4>
                                                </div>
                                                <p className="mt-2 line-clamp-2 text-[11px] leading-relaxed text-muted-foreground sm:text-xs">
                                                    {servicio.descripcion}
                                                </p>
                                            </div>

                                            <div className="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-xs font-bold">
                                                <span className="text-primary dark:text-rose-400">
                                                    {servicio.precio !== null &&
                                                    servicio.precio !==
                                                        undefined
                                                        ? `${servicio.moneda || '$'} ${servicio.precio.toFixed(2)}`
                                                        : 'Servicio exclusivo'}
                                                </span>
                                                <span className="flex items-center gap-1 text-[11px] text-muted-foreground group-hover:text-foreground">
                                                    <CheckCircle2 className="size-3.5 text-emerald-500" />
                                                    <span>Disponible</span>
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
};

export default ServicesSection;
