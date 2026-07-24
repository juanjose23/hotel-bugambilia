import { CheckCircle2 } from 'lucide-react';
export interface PasoReserva {
    id: number;
    titulo: string;
}
interface PropiedadesIndicadorPasosReserva {
    pasoActual: number;
    pasos: PasoReserva[];
    alSeleccionarPaso: (paso: number) => void;
}
export const IndicadorPasosReserva = ({
    pasoActual,
    pasos,
    alSeleccionarPaso,
}: PropiedadesIndicadorPasosReserva) => {
    const progreso =
        pasos.length > 1 ? ((pasoActual - 1) / (pasos.length - 1)) * 88 : 0;

    return (
        <div className="mb-8 rounded-3xl border border-border/80 bg-card p-4 shadow-sm md:p-6">
            <div className="relative mx-auto flex max-w-xl items-center justify-between">
                <div className="absolute top-5 right-6 left-6 -z-0 h-0.5 -translate-y-1/2 bg-border/60" />
                <div
                    className="absolute top-5 left-6 -z-0 h-0.5 -translate-y-1/2 bg-bugambilia-600 transition-all duration-500"
                    style={{ width: `${progreso}%` }}
                />

                {pasos.map((paso) => {
                    const completado = paso.id < pasoActual;
                    const activo = paso.id === pasoActual;
                    const disponible = paso.id <= pasoActual;

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
                                    : 'cursor-not-allowed opacity-70'
                            }`}
                        >
                            <span
                                className={`flex h-10 w-10 items-center justify-center rounded-2xl text-xs font-black transition-all ${
                                    completado
                                        ? 'bg-emerald-600 text-white shadow-md'
                                        : activo
                                          ? 'scale-110 bg-bugambilia-600 text-white shadow-lg ring-4 ring-bugambilia-500/20'
                                          : 'border border-border bg-background text-muted-foreground'
                                }`}
                            >
                                {completado ? (
                                    <CheckCircle2 className="h-5 w-5" />
                                ) : (
                                    paso.id
                                )}
                            </span>
                            <span
                                className={`text-[11px] font-extrabold tracking-tight ${
                                    activo
                                        ? 'font-serif text-bugambilia-600 italic dark:text-bugambilia-400'
                                        : 'text-muted-foreground'
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
