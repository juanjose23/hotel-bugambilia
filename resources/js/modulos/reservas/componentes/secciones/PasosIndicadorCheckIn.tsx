import { UserCheck, Users, CreditCard, FileCheck2 } from 'lucide-react';

interface PropiedadesPasosIndicadorCheckIn {
    pasoActual: number;
    alSeleccionarPaso?: (paso: number) => void;
}

export const PasosIndicadorCheckIn = ({
    pasoActual,
    alSeleccionarPaso,
}: PropiedadesPasosIndicadorCheckIn) => {
    const pasos = [
        { num: 1, label: 'Titular', icono: UserCheck },
        { num: 2, label: 'Acompañantes', icono: Users },
        { num: 3, label: 'Garantía & Crédito', icono: CreditCard },
        { num: 4, label: 'Firma & Políticas', icono: FileCheck2 },
    ];

    return (
        <div className="mb-8 grid grid-cols-2 gap-2 font-sans sm:grid-cols-4">
            {pasos.map((p) => {
                const esActivo = pasoActual === p.num;
                const esCompletado = pasoActual > p.num;

                return (
                    <button
                        key={p.num}
                        type="button"
                        onClick={() =>
                            alSeleccionarPaso && alSeleccionarPaso(p.num)
                        }
                        disabled={!alSeleccionarPaso}
                        className={`flex items-center gap-3 rounded-2xl border p-3.5 text-left transition-all ${
                            esActivo
                                ? 'border-bugambilia-600 bg-bugambilia-500/10 text-bugambilia-600 shadow-xs dark:border-bugambilia-400 dark:text-bugambilia-400'
                                : esCompletado
                                  ? 'border-emerald-500/40 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400'
                                  : 'border-border/60 bg-card text-muted-foreground'
                        }`}
                    >
                        <div
                            className={`flex size-8 shrink-0 items-center justify-center rounded-xl text-xs font-black ${
                                esActivo
                                    ? 'bg-bugambilia-600 text-white dark:bg-bugambilia-500'
                                    : esCompletado
                                      ? 'bg-emerald-500 text-white'
                                      : 'bg-muted text-muted-foreground'
                            }`}
                        >
                            <p.icono className="size-4" />
                        </div>
                        <div className="hidden sm:block">
                            <span className="block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Paso {p.num}
                            </span>
                            <span className="block truncate text-xs font-black text-foreground">
                                {p.label}
                            </span>
                        </div>
                    </button>
                );
            })}
        </div>
    );
};
