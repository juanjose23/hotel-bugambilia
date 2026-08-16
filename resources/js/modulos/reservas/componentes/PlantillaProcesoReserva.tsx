import React from 'react';
import { EncabezadoRecursoReserva } from './EncabezadoRecursoReserva';
import { IndicadorPasosReserva } from './IndicadorPasosReserva';
import type { PasoReserva } from './IndicadorPasosReserva';
import { ListadoErroresFormulario } from './ListadoErroresFormulario';
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
    return (
        <div className="min-h-screen bg-background py-8 text-foreground transition-colors duration-300">
            <div className="container mx-auto flex max-w-4xl flex-col gap-6 px-4">
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

                <form onSubmit={onSubmit} className="flex flex-col gap-6">
                    <ListadoErroresFormulario errores={errores} />

                    <main className="flex flex-col gap-6">{children}</main>

                    <NavegacionReserva
                        pasoActual={pasoActual}
                        totalPasos={totalPasos}
                        procesando={procesando}
                        alRetroceder={onRetroceder}
                    />
                </form>
            </div>
        </div>
    );
}
