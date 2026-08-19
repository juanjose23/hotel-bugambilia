import { MapPin, BedDouble, Lock } from 'lucide-react';
import React, { useState } from 'react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { EncabezadoRecursoReserva } from './EncabezadoRecursoReserva';
import { IndicadorPasosReserva } from './IndicadorPasosReserva';
import type { PasoReserva } from './IndicadorPasosReserva';
import { ListadoErroresFormulario } from './ListadoErroresFormulario';
import { ModalCondicionesPagoCancelacion } from './ModalCondicionesPagoCancelacion';
import { NavegacionReserva } from './NavegacionReserva';

interface PropiedadesPlantillaProcesoReserva {
    nombreRecurso: string;
    categoriaRecurso: string;
    tipoEtiqueta?: string;
    ubicacionRecurso: string;
    camasRecurso?: string;
    imagenPrincipal: string;
    slugRecurso: string;
    rutaRetorno?: string;
    promoAplicada?: string | null;
    pasoActual: number;
    totalPasos?: number;
    pasos: PasoReserva[];
    errores: Record<string, string>;
    procesando: boolean;
    onRetroceder: () => void;
    onIrAlPaso: (paso: number) => void;
    onSubmit: (e: React.SubmitEvent) => void;
    children: React.ReactNode;
}

export function PlantillaProcesoReserva({
    nombreRecurso,
    categoriaRecurso,
    tipoEtiqueta,
    ubicacionRecurso,
    camasRecurso,
    imagenPrincipal,
    slugRecurso,
    rutaRetorno,
    promoAplicada,
    pasoActual,
    totalPasos = 4,
    pasos,
    errores,
    procesando,
    onRetroceder,
    onIrAlPaso,
    onSubmit,
    children,
}: PropiedadesPlantillaProcesoReserva) {
    const [modalCondicionesAbierto, setModalCondicionesAbierto] =
        useState(false);

    return (
        <div className="min-h-screen bg-background py-6 font-sans text-foreground transition-colors duration-300">
            <div className="container mx-auto max-w-6xl px-3 sm:px-6">
                <EncabezadoRecursoReserva
                    nombre={nombreRecurso}
                    categoria={categoriaRecurso}
                    tipoEtiqueta={tipoEtiqueta}
                    ubicacion={ubicacionRecurso}
                    camas={camasRecurso}
                    imagenPrincipal={imagenPrincipal}
                    slug={slugRecurso}
                    rutaRetorno={rutaRetorno}
                    promoAplicada={promoAplicada}
                    pasoActual={pasoActual}
                    totalPasos={totalPasos}
                />

                <IndicadorPasosReserva
                    pasoActual={pasoActual}
                    pasos={pasos}
                    alSeleccionarPaso={onIrAlPaso}
                />

                {/* Layout Principal Grid 2 Columnas (Paso Wizard + Tarjeta Lateral de Resumen) */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
                    {/* Columna Principal: Contenido del Wizard */}
                    <div className="lg:col-span-8">
                        <form
                            onSubmit={onSubmit}
                            className="flex flex-col gap-6"
                        >
                            <ListadoErroresFormulario errores={errores} />

                            <main
                                key={pasoActual}
                                className="animate-in fade-in-50 slide-in-from-bottom-2 flex flex-col gap-6 duration-300"
                            >
                                {children}
                            </main>

                            <NavegacionReserva
                                pasoActual={pasoActual}
                                totalPasos={totalPasos}
                                procesando={procesando}
                                alRetroceder={onRetroceder}
                            />
                        </form>
                    </div>

                    {/* Columna Lateral Sticky: Tarjeta Barceló-Style "SU SELECCIÓN" */}
                    <div className="sticky top-6 hidden space-y-4 lg:col-span-4 lg:block">
                        <div className="overflow-hidden rounded-3xl border border-border/80 bg-card shadow-2xs">
                            <div className="flex items-center justify-between border-b border-border/60 bg-muted/40 px-4 py-3">
                                <span className="text-xs font-black tracking-wider text-foreground uppercase">
                                    SU SELECCIÓN
                                </span>
                                <Badge className="bg-bugambilia-600 text-[10px] font-black text-white">
                                    {categoriaRecurso}
                                </Badge>
                            </div>

                            <div className="relative h-32 w-full">
                                <img
                                    src={imagenPrincipal}
                                    alt={nombreRecurso}
                                    className="h-full w-full object-cover"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent" />
                                <div className="absolute right-3.5 bottom-2.5 left-3.5">
                                    <h3 className="truncate text-sm font-black text-white">
                                        {nombreRecurso}
                                    </h3>
                                </div>
                            </div>

                            <div className="space-y-3 p-4 text-xs">
                                <div className="flex items-center justify-between border-b border-border/50 pb-2">
                                    <span className="font-medium text-muted-foreground">
                                        Ubicación:
                                    </span>
                                    <span className="flex items-center gap-1 font-extrabold text-foreground">
                                        <MapPin className="size-3 text-bugambilia-600 dark:text-bugambilia-400" />
                                        {ubicacionRecurso || 'Ala Principal'}
                                    </span>
                                </div>

                                {camasRecurso && (
                                    <div className="flex items-center justify-between border-b border-border/50 pb-2">
                                        <span className="font-medium text-muted-foreground">
                                            Capacidad:
                                        </span>
                                        <span className="flex items-center gap-1 font-extrabold text-foreground">
                                            <BedDouble className="size-3 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {camasRecurso}
                                        </span>
                                    </div>
                                )}

                                <div className="space-y-1.5 rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-3">
                                    <div className="flex items-center justify-between">
                                        <span className="flex items-center gap-1 text-[11px] font-extrabold text-bugambilia-700 dark:text-bugambilia-300">
                                            <Lock className="size-3.5 text-bugambilia-600" />
                                            Garantía Barceló Resort
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setModalCondicionesAbierto(true)
                                            }
                                            className="cursor-pointer text-[10px] font-black text-bugambilia-600 underline hover:text-bugambilia-700"
                                        >
                                            Ver condiciones
                                        </button>
                                    </div>
                                    <p className="text-[10px] leading-tight text-muted-foreground">
                                        Sin sorpresas ni cargos ocultos.
                                        Cancelación flexible disponible en su
                                        Portal.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de Condiciones de Pago y Cancelación */}
            <ModalCondicionesPagoCancelacion
                estaAbierto={modalCondicionesAbierto}
                alCerrar={() => setModalCondicionesAbierto(false)}
            />
        </div>
    );
}
