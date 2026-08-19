import type { LucideIcon } from 'lucide-react';
import {
    CheckCircle2,
    Calendar,
    Users,
    Sparkles,
    CreditCard,
} from 'lucide-react';

export interface PasoReserva {
    id: number;
    titulo: string;
}

interface PropiedadesIndicadorPasosReserva {
    pasoActual: number;
    pasos: PasoReserva[];
    alSeleccionarPaso: (paso: number) => void;
}

const ICONOS_PASO: Record<number, LucideIcon> = {
    1: Calendar,
    2: Users,
    3: Sparkles,
    4: CreditCard,
};

export const IndicadorPasosReserva = ({
    pasoActual,
    pasos,
    alSeleccionarPaso,
}: PropiedadesIndicadorPasosReserva) => {
    const progreso =
        pasos.length > 1 ? ((pasoActual - 1) / (pasos.length - 1)) * 100 : 0;

    return (
        <div className="mb-6 rounded-3xl border border-border/80 bg-card p-4 shadow-2xs md:p-5">
            {/* Indicador Móvil Compacto */}
            <div className="flex items-center justify-between px-1 sm:hidden">
                <span className="text-xs font-black text-bugambilia-600 uppercase dark:text-bugambilia-400">
                    Paso {pasoActual} de {pasos.length}:{' '}
                    {pasos[pasoActual - 1]?.titulo}
                </span>
                <span className="font-mono text-xs font-bold text-muted-foreground">
                    {Math.round((pasoActual / pasos.length) * 100)}%
                </span>
            </div>
            <div className="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-muted sm:hidden">
                <div
                    className="h-full bg-gradient-to-r from-bugambilia-600 to-bugambilia-500 transition-all duration-500"
                    style={{ width: `${(pasoActual / pasos.length) * 100}%` }}
                />
            </div>

            {/* Indicador Desktop & Tablet Completo */}
            <div className="relative mx-auto hidden max-w-2xl items-center justify-between sm:flex">
                <div className="absolute top-5 right-6 left-6 -z-0 h-1 -translate-y-1/2 rounded-full bg-muted" />
                <div
                    className="absolute top-5 left-6 -z-0 h-1 -translate-y-1/2 rounded-full bg-gradient-to-r from-bugambilia-600 to-emerald-500 transition-all duration-500"
                    style={{ width: `${progreso * 0.88}%` }}
                />

                {pasos.map((paso) => {
                    const completado = paso.id < pasoActual;
                    const activo = paso.id === pasoActual;
                    const disponible = paso.id <= pasoActual;
                    const IconoPaso = ICONOS_PASO[paso.id] || Calendar;

                    return (
                        <button
                            key={paso.id}
                            type="button"
                            disabled={!disponible}
                            onClick={() =>
                                completado && alSeleccionarPaso(paso.id)
                            }
                            className={`relative z-10 flex flex-col items-center gap-1.5 transition-all ${
                                disponible
                                    ? 'cursor-pointer'
                                    : 'cursor-not-allowed opacity-60'
                            }`}
                        >
                            <span
                                className={`flex size-10 items-center justify-center rounded-2xl text-xs font-black transition-all ${
                                    completado
                                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20'
                                        : activo
                                          ? 'scale-110 bg-bugambilia-600 text-white shadow-lg ring-4 shadow-bugambilia-600/30 ring-bugambilia-500/20'
                                          : 'border border-border/80 bg-background text-muted-foreground'
                                }`}
                            >
                                {completado ? (
                                    <CheckCircle2 className="size-5" />
                                ) : (
                                    <IconoPaso className="size-4.5" />
                                )}
                            </span>
                            <span
                                className={`text-[11px] font-extrabold tracking-tight ${
                                    activo
                                        ? 'font-black text-bugambilia-600 dark:text-bugambilia-400'
                                        : completado
                                          ? 'font-bold text-foreground'
                                          : 'font-medium text-muted-foreground'
                                }`}
                            >
                                {paso.titulo}
                            </span>
                        </button>
                    );
                })}
            </div>
        </div>
    );
};
