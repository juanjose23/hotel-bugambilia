import { Link } from '@inertiajs/react';
import { ArrowLeft, BedDouble, MapPin, BadgeCheck } from 'lucide-react';
import React from 'react';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesEncabezadoRecurso {
    nombre: string;
    categoria: string;
    tipoEtiqueta?: string;
    ubicacion: string;
    camas?: string;
    imagenPrincipal: string;
    slug: string;
    rutaRetorno?: string;
    promoAplicada?: string | null;
    pasoActual: number;
    totalPasos?: number;
}

export function EncabezadoRecursoReserva({
    nombre,
    categoria,
    tipoEtiqueta = 'Tipo de habitación',
    ubicacion,
    camas,
    imagenPrincipal,
    slug,
    rutaRetorno = `/habitaciones/${slug}`,
    promoAplicada,
    pasoActual,
    totalPasos = 4,
}: PropiedadesEncabezadoRecurso) {
    return (
        <div className="mb-6 space-y-4">
            {/* Botón de Retorno Móvil / Superior */}
            <div className="flex items-center justify-between">
                <Link
                    href={rutaRetorno}
                    className="inline-flex items-center text-xs font-bold text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                >
                    <ArrowLeft className="mr-1.5 h-4 w-4" />
                    Volver a {categoria}
                </Link>
                <span className="text-[11px] font-extrabold tracking-widest text-muted-foreground uppercase">
                    Paso {pasoActual} de {totalPasos}
                </span>
            </div>

            {/* Encabezado Botánico Fino Hotel Bugambilias */}
            <div className="flex items-center gap-4 rounded-3xl border border-border bg-card p-4 shadow-xs md:p-6">
                <img
                    src={imagenPrincipal}
                    alt={nombre}
                    className="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-sm md:h-24 md:w-28"
                />
                <div className="min-w-0 flex-1">
                    <div className="mb-1 flex flex-wrap items-center gap-2">
                        <Badge className="bg-bugambilia-500/10 text-[10px] font-extrabold text-bugambilia-700 dark:text-bugambilia-300">
                            {categoria}
                        </Badge>
                        <Badge className="bg-emerald-500/10 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                            {tipoEtiqueta}
                        </Badge>
                    </div>
                    <h1 className="truncate text-lg font-black text-foreground md:text-2xl">
                        {nombre}
                    </h1>
                    <p className="mt-0.5 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                        {ubicacion && (
                            <span className="inline-flex items-center">
                                <MapPin className="mr-1 h-3.5 w-3.5 shrink-0 text-bugambilia-600" />
                                {ubicacion}
                            </span>
                        )}
                        {camas && (
                            <span className="inline-flex items-center">
                                <BedDouble className="mr-1 h-3.5 w-3.5 shrink-0 text-bugambilia-600" />
                                {camas}
                            </span>
                        )}
                    </p>
                </div>
            </div>

            {/* Banner de Promoción */}
            {promoAplicada && (
                <div className="flex items-center justify-between rounded-2xl border border-amber-500/40 bg-amber-500/10 p-4 font-sans text-xs font-bold text-amber-700 dark:text-amber-300">
                    <div className="flex items-center gap-2.5">
                        <BadgeCheck className="h-4 w-4 shrink-0 text-amber-500" />
                        <span>
                            ¡Promoción <strong>{promoAplicada}</strong> aplicada
                            correctamente a su reserva!
                        </span>
                    </div>
                    <span className="rounded-full bg-amber-500/20 px-3 py-1 text-[10px] font-black text-amber-600 uppercase dark:text-amber-400">
                        Descuento Activo
                    </span>
                </div>
            )}
        </div>
    );
}
