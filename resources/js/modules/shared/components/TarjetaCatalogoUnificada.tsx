import { Link } from '@inertiajs/react';
import { Users, BedDouble, Maximize, ArrowRight, Tag } from 'lucide-react';
import React from 'react';
import { Badge } from '@/modules/shared/ui/insignia';

export interface PropiedadesTarjetaCatalogoUnificada {
    id: number;
    slug?: string;
    nombre: string;
    codigo?: string;
    categoria?: string;
    descripcion?: string;
    precio?: number | null;
    moneda?: string;
    tipoTarifaLabel?: string;
    imagen?: string;
    capacidadHuespedes?: number;
    capacidadCamas?: string;
    medidasMetros?: string;
    tipo?: 'habitacion' | 'espacio' | 'servicio';
    hrefDetalle: string;
    className?: string;
}

const formatearMonto = (val?: number | null) => {
    if (val === undefined || val === null) {
        return '0.00';
    }

    return Number(val).toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const TarjetaCatalogoUnificada: React.FC<
    PropiedadesTarjetaCatalogoUnificada
> = ({
    nombre,
    codigo,
    categoria,
    descripcion,
    precio,
    moneda = '$',
    tipoTarifaLabel,
    imagen = '/images/main-room.webp',
    capacidadHuespedes,
    capacidadCamas,
    medidasMetros,
    hrefDetalle,
    className = '',
}) => {
    const tienePrecio = precio !== undefined && precio !== null && precio > 0;

    return (
        <article
            className={`group shadow-airbnb hover:shadow-airbnb-hover relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card font-sans transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/40 ${className}`}
        >
            <div>
                {/* Imagen del Item */}
                <div className="relative aspect-4/3 overflow-hidden bg-muted">
                    <img
                        src={imagen}
                        alt={nombre}
                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

                    {/* Insignia Categoría */}
                    {categoria && (
                        <div className="absolute top-3 left-3 z-10">
                            <Badge
                                variant="secondary"
                                className="border border-white/20 bg-black/70 px-3 py-1 text-[10px] font-extrabold tracking-wider text-white uppercase backdrop-blur-md"
                            >
                                {categoria}
                            </Badge>
                        </div>
                    )}

                    {/* Código Item */}
                    {codigo && (
                        <div className="absolute right-3 bottom-3 z-10">
                            <span className="inline-flex items-center gap-1 rounded-full bg-amber-400 px-2.5 py-0.5 text-[10px] font-black text-black uppercase">
                                <Tag className="h-3 w-3 text-black" />
                                {codigo}
                            </span>
                        </div>
                    )}
                </div>

                {/* Contenido Principal */}
                <div className="p-5">
                    <h3 className="mb-2 text-base font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                        {nombre}
                    </h3>

                    {descripcion && (
                        <p className="mb-4 line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground">
                            {descripcion}
                        </p>
                    )}

                    {/* Ficha de Características Rápidas (Huéspedes, Camas, Superficie) */}
                    {(capacidadHuespedes ||
                        capacidadCamas ||
                        medidasMetros) && (
                        <div className="mb-4 flex flex-wrap items-center gap-3 border-t border-border/40 pt-3 text-xs text-muted-foreground">
                            {capacidadHuespedes && (
                                <span className="inline-flex items-center gap-1 font-semibold">
                                    <Users className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                    {capacidadHuespedes} p.
                                </span>
                            )}
                            {capacidadCamas && (
                                <span className="inline-flex items-center gap-1 font-semibold">
                                    <BedDouble className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                    {capacidadCamas}
                                </span>
                            )}
                            {medidasMetros && (
                                <span className="inline-flex items-center gap-1 font-semibold">
                                    <Maximize className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                    {medidasMetros}
                                </span>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Footer de Tarjeta con Tarifa y Acción */}
            <div className="flex items-center justify-between border-t border-border/40 px-5 py-4">
                <div>
                    <span className="block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                        {tipoTarifaLabel || 'Tarifa'}
                    </span>
                    <div className="text-base font-black text-foreground">
                        {tienePrecio ? (
                            <>
                                {moneda} {formatearMonto(precio)}
                            </>
                        ) : (
                            <span className="text-emerald-600 dark:text-emerald-400">
                                Acceso Libre
                            </span>
                        )}
                    </div>
                </div>

                <Link
                    href={hrefDetalle}
                    className="inline-flex items-center gap-1 text-xs font-extrabold text-bugambilia-600 transition-colors group-hover:underline dark:text-bugambilia-400"
                >
                    <span>Ver Detalles</span>
                    <ArrowRight className="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                </Link>
            </div>
        </article>
    );
};
