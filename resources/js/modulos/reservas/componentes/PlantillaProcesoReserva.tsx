import React, { useState } from 'react';
import { EncabezadoRecursoReserva } from './EncabezadoRecursoReserva';
import { IndicadorPasosReserva } from './IndicadorPasosReserva';
import type { PasoReserva } from './IndicadorPasosReserva';
import { ListadoErroresFormulario } from './ListadoErroresFormulario';
import { NavegacionReserva } from './NavegacionReserva';
import { ModalCondicionesPagoCancelacion } from './ModalCondicionesPagoCancelacion';
import { Calendar, ShieldCheck, MapPin, BedDouble, Info, CheckCircle2, Lock } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';

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
    const [modalCondicionesAbierto, setModalCondicionesAbierto] = useState(false);

    return (
        <div className="min-h-screen bg-background py-6 text-foreground font-sans transition-colors duration-300">
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
                        <form onSubmit={onSubmit} className="flex flex-col gap-6">
                            <ListadoErroresFormulario errores={errores} />

                            <main key={pasoActual} className="animate-in fade-in-50 slide-in-from-bottom-2 duration-300 flex flex-col gap-6">
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
                    <div className="hidden lg:block lg:col-span-4 sticky top-6 space-y-4">
                        <div className="overflow-hidden rounded-3xl border border-border/80 bg-card shadow-2xs">
                            <div className="border-b border-border/60 bg-muted/40 px-4 py-3 flex items-center justify-between">
                                <span className="text-xs font-black uppercase text-foreground tracking-wider">
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
                                <div className="absolute bottom-2.5 left-3.5 right-3.5">
                                    <h3 className="text-sm font-black text-white truncate">
                                        {nombreRecurso}
                                    </h3>
                                </div>
                            </div>

                            <div className="p-4 space-y-3 text-xs">
                                <div className="flex items-center justify-between border-b border-border/50 pb-2">
                                    <span className="text-muted-foreground font-medium">Ubicación:</span>
                                    <span className="font-extrabold text-foreground flex items-center gap-1">
                                        <MapPin className="size-3 text-bugambilia-600 dark:text-bugambilia-400" />
                                        {ubicacionRecurso || 'Ala Principal'}
                                    </span>
                                </div>

                                {camasRecurso && (
                                    <div className="flex items-center justify-between border-b border-border/50 pb-2">
                                        <span className="text-muted-foreground font-medium">Capacidad:</span>
                                        <span className="font-extrabold text-foreground flex items-center gap-1">
                                            <BedDouble className="size-3 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {camasRecurso}
                                        </span>
                                    </div>
                                )}

                                <div className="rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-3 space-y-1.5">
                                    <div className="flex items-center justify-between">
                                        <span className="text-[11px] font-extrabold text-bugambilia-700 dark:text-bugambilia-300 flex items-center gap-1">
                                            <Lock className="size-3.5 text-bugambilia-600" />
                                            Garantía Barceló Resort
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => setModalCondicionesAbierto(true)}
                                            className="text-[10px] font-black text-bugambilia-600 underline hover:text-bugambilia-700 cursor-pointer"
                                        >
                                            Ver condiciones
                                        </button>
                                    </div>
                                    <p className="text-[10px] text-muted-foreground leading-tight">
                                        Sin sorpresas ni cargos ocultos. Cancelación flexible disponible en su Portal.
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


