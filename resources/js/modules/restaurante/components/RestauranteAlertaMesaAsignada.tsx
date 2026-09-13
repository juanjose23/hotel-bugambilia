import {
    ChevronDown,
    ChevronUp,
    MapPin,
    ShieldCheck,
    Sparkles,
    UtensilsCrossed,
} from 'lucide-react';
import type { DisponibilidadMesasResponse } from '../services/restauranteService';

interface RestauranteBotonVerPlanoProps {
    clases: string;
    mostrarPlano: boolean;
    alAlternarPlano: () => void;
}

const RestauranteBotonVerPlano = ({
    clases,
    mostrarPlano,
    alAlternarPlano,
}: RestauranteBotonVerPlanoProps) => (
    <button
        type="button"
        onClick={alAlternarPlano}
        className={`flex w-full items-center justify-between rounded-xl bg-background/60 hover:bg-background ${clases} cursor-pointer px-3 py-1.5 text-[11px] font-bold transition-colors`}
    >
        <span className="flex items-center gap-1.5">
            <MapPin className="size-3.5" />
            {mostrarPlano
                ? 'Ocultar Plano de Mesas'
                : 'Ver Ubicación en el Plano de Mesas'}
        </span>
        {mostrarPlano ? (
            <ChevronUp className="size-3.5" />
        ) : (
            <ChevronDown className="size-3.5" />
        )}
    </button>
);

interface RestauranteAlertaMesaAsignadaProps {
    disponibilidad?: DisponibilidadMesasResponse | null;
    adultosSeleccionados: number;
    mostrarPlano: boolean;
    alAlternarPlano: () => void;
}

export const RestauranteAlertaMesaAsignada = ({
    disponibilidad,
    adultosSeleccionados,
    mostrarPlano,
    alAlternarPlano,
}: RestauranteAlertaMesaAsignadaProps) => {
    const tieneMesasUnidas = Boolean(
        disponibilidad?.requiere_union &&
        disponibilidad?.mesas_sugeridas_union &&
        disponibilidad.mesas_sugeridas_union.length > 1,
    );

    const mesaIndividualAsignada = !disponibilidad?.requiere_union
        ? disponibilidad?.mesa_asignada
        : null;

    if (tieneMesasUnidas && disponibilidad) {
        return (
            <div className="animate-in fade-in space-y-2 rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-3.5 text-xs text-foreground duration-300">
                <div className="flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/20 text-primary dark:text-rose-400">
                            <Sparkles className="size-4" />
                        </div>
                        <span className="text-xs font-black tracking-wide text-primary uppercase dark:text-rose-300">
                            Unión Automática de Mesas Activa
                        </span>
                    </div>
                    <span className="rounded-full border border-primary/20 bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold text-primary dark:text-rose-300">
                        Grupo de {adultosSeleccionados} pers.
                    </span>
                </div>

                <p className="text-[11px] text-muted-foreground">
                    Para acomodar a tu grupo con la máxima comodidad, uniremos
                    las mesas en{' '}
                    <strong className="text-foreground">
                        {disponibilidad.zona_asignada || 'Salón Interior'}
                    </strong>
                    :
                </p>

                <div className="flex flex-wrap items-center gap-1.5 pt-0.5">
                    {disponibilidad.mesas_sugeridas_union.map((m, idx) => (
                        <span
                            key={m.id}
                            className="inline-flex items-center gap-1.5"
                        >
                            <span className="inline-flex items-center gap-1.5 rounded-xl border border-primary/30 bg-card px-2.5 py-1 text-[11px] font-bold text-foreground shadow-xs">
                                <UtensilsCrossed className="size-3 text-primary" />
                                <span>{m.nombre}</span>
                                <span className="text-[10px] font-normal text-muted-foreground">
                                    ({m.capacidad}p)
                                </span>
                            </span>
                            {idx <
                                disponibilidad.mesas_sugeridas_union.length -
                                    1 && (
                                <span className="text-xs font-black text-primary">
                                    +
                                </span>
                            )}
                        </span>
                    ))}
                </div>

                <RestauranteBotonVerPlano
                    clases="border border-primary/20 text-primary dark:text-rose-400"
                    mostrarPlano={mostrarPlano}
                    alAlternarPlano={alAlternarPlano}
                />
            </div>
        );
    }

    if (mesaIndividualAsignada) {
        return (
            <div className="animate-in fade-in space-y-2 rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-3 text-xs text-foreground duration-300">
                <div className="flex items-center gap-3">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <ShieldCheck className="size-4" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center justify-between gap-1">
                            <span className="truncate font-bold text-foreground">
                                Mesa asignada: {mesaIndividualAsignada.nombre}
                            </span>
                            <span className="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                                Confirmada
                            </span>
                        </div>
                        <p className="mt-0.5 text-[11px] text-muted-foreground">
                            {mesaIndividualAsignada.zona || 'Salón Principal'} ·
                            Capacidad hasta {mesaIndividualAsignada.capacidad}{' '}
                            personas
                        </p>
                    </div>
                </div>

                <RestauranteBotonVerPlano
                    clases="border border-emerald-500/20 text-emerald-700 dark:text-emerald-300"
                    mostrarPlano={mostrarPlano}
                    alAlternarPlano={alAlternarPlano}
                />
            </div>
        );
    }

    return null;
};

export default RestauranteAlertaMesaAsignada;
