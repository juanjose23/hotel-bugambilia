import { CheckCircle2, Sparkles, Users, Utensils } from 'lucide-react';
import type { MesaData } from '../types';

interface RestauranteMesaCardInternaProps {
    mesa: MesaData;
    esSeleccionada: boolean;
    esUnida: boolean;
    alSeleccionar: (mesa: MesaData) => void;
}

export const RestauranteMesaCardInterna = ({
    mesa,
    esSeleccionada,
    esUnida,
    alSeleccionar,
}: RestauranteMesaCardInternaProps) => {
    const ordenNum = mesa.orden ?? mesa.id;

    return (
        <div
            onClick={() => alSeleccionar(mesa)}
            className={`group relative flex cursor-pointer flex-col items-center justify-between rounded-2xl border p-3 text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-md ${
                esSeleccionada
                    ? 'border-primary bg-primary/15 ring-2 ring-primary dark:bg-rose-950/40'
                    : esUnida
                      ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/30'
                      : 'border-border/80 bg-card hover:border-primary/50 dark:hover:border-rose-500/50'
            }`}
        >
            <div className="absolute top-2 left-2 flex size-5 items-center justify-center rounded-full bg-muted text-[10px] font-black text-muted-foreground shadow-xs">
                #{ordenNum}
            </div>

            <div className="absolute top-2 right-2 flex items-center gap-0.5 rounded-full border border-border/40 bg-background/80 px-1.5 py-0.5 text-[10px] font-bold text-muted-foreground backdrop-blur-xs">
                <Users className="size-2.5 text-primary dark:text-rose-400" />
                <span>{mesa.capacidad}</span>
            </div>

            <div className="my-2 mt-4 flex items-center justify-center">
                <div
                    className={`flex size-14 items-center justify-center transition-all duration-300 group-hover:scale-105 ${
                        mesa.tipo_mesa === 'redonda'
                            ? 'rounded-full'
                            : mesa.tipo_mesa === 'rectangular'
                              ? 'aspect-16/10 h-11 w-16 rounded-xl'
                              : 'rounded-xl'
                    } ${
                        esSeleccionada
                            ? 'bg-primary text-primary-foreground shadow-md'
                            : esUnida
                              ? 'bg-amber-500 text-white shadow-md'
                              : 'border border-border/80 bg-muted/80 text-foreground group-hover:border-primary/40'
                    }`}
                >
                    <Utensils className="size-5" />
                </div>
            </div>

            <span className="line-clamp-1 text-xs font-black tracking-tight text-foreground">
                {mesa.nombre}
            </span>
            <span className="text-[10px] text-muted-foreground capitalize">
                {mesa.tipo_mesa || 'Cuadrada'}
            </span>

            <div className="mt-2 flex w-full items-center justify-center gap-1 border-t border-border/40 pt-1.5">
                {esSeleccionada ? (
                    <span className="inline-flex items-center gap-1 text-[10px] font-black text-primary dark:text-rose-400">
                        <CheckCircle2 className="size-3" /> Seleccionada
                    </span>
                ) : esUnida ? (
                    <span className="inline-flex items-center gap-1 text-[10px] font-black text-amber-600 dark:text-amber-400">
                        <Sparkles className="size-3" /> Mesa Unida
                    </span>
                ) : (
                    <span className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                        Disponible
                    </span>
                )}
            </div>
        </div>
    );
};

export default RestauranteMesaCardInterna;
