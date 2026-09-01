import { CheckCircle2 } from 'lucide-react';

export const CalendarioLeyenda = () => {
    return (
        <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-border/60 pt-4 text-xs font-bold text-muted-foreground">
            <div className="flex items-center gap-2">
                <span className="size-2.5 rounded-full bg-primary" />
                <span>Fechas seleccionadas</span>
            </div>
            <div className="flex items-center gap-2">
                <span className="size-2.5 rounded-full bg-destructive" />
                <span>Agotado / No disponible</span>
            </div>
            <div className="flex items-center gap-2">
                <CheckCircle2 className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                <span>Disponible para reserva</span>
            </div>
        </div>
    );
};
