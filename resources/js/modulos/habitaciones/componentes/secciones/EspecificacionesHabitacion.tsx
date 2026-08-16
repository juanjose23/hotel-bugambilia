import { Users, Maximize, Sparkles, Eye } from 'lucide-react';
import { Card } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesEspecificacionesHabitacion {
    capacidad?: number;
    medidas?: string;
    vistas?: string[];
    categoria?: string;
}

export const EspecificacionesHabitacion = ({
    capacidad = 2,
    medidas = '32 m²',
    vistas = [],
    categoria = 'Boutique Corporativo',
}: PropiedadesEspecificacionesHabitacion) => {
    return (
        <Card className="w-full max-w-full overflow-hidden rounded-3xl border border-border/80 bg-card p-4 font-sans shadow-xs sm:p-6">
            <div className="mb-3 grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3">
                <div className="flex min-w-0 flex-col justify-center rounded-2xl border border-border/80 bg-background p-3">
                    <span className="mb-0.5 block text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                        Capacidad
                    </span>
                    <span className="inline-flex items-center gap-1 truncate text-xs font-black text-foreground">
                        <Users className="size-3.5 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                        <span>Hasta {capacidad} pers.</span>
                    </span>
                </div>

                <div className="flex min-w-0 flex-col justify-center rounded-2xl border border-border/80 bg-background p-3">
                    <span className="mb-0.5 block text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                        Categoría
                    </span>
                    <span className="inline-flex items-center gap-1 truncate text-xs font-black text-foreground">
                        <Sparkles className="size-3.5 shrink-0 text-amber-500" />
                        <span className="truncate">{categoria}</span>
                    </span>
                </div>

                <div className="col-span-2 flex min-w-0 flex-col justify-center rounded-2xl border border-border/80 bg-background p-3 sm:col-span-1">
                    <span className="mb-0.5 block text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                        Superficie
                    </span>
                    <span className="inline-flex items-center gap-1 truncate text-xs font-black text-foreground">
                        <Maximize className="size-3.5 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                        <span>{medidas}</span>
                    </span>
                </div>
            </div>

            {vistas.length > 0 && (
                <div className="flex flex-wrap items-center gap-1.5 border-t border-border/40 pt-2.5 sm:gap-2 sm:pt-3">
                    <span className="text-xs font-bold text-muted-foreground">
                        Vistas:
                    </span>
                    {vistas.map((vista, idx) => (
                        <span
                            key={idx}
                            className="inline-flex items-center gap-1 rounded-full border border-border/80 bg-background px-2.5 py-0.5 text-[11px] font-extrabold text-foreground sm:px-3 sm:py-1 sm:text-xs"
                        >
                            <Eye className="size-3 text-bugambilia-600 dark:text-bugambilia-400" />
                            {vista}
                        </span>
                    ))}
                </div>
            )}
        </Card>
    );
};

export default EspecificacionesHabitacion;
