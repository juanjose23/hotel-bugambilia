import { Volume2, CheckCircle2 } from 'lucide-react';
import { Card } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesEquipamientoEspacio {
    equipamiento: string[];
}

export const EquipamientoEspacio = ({
    equipamiento = [],
}: PropiedadesEquipamientoEspacio) => {
    if (equipamiento.length === 0) {
        return null;
    }

    return (
        <Card className="rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xs">
            <div className="mb-4 flex items-center gap-2">
                <Volume2 className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
                <h3 className="text-base font-black text-foreground">
                    Equipamiento & Audiovisuales Incluidos
                </h3>
            </div>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {equipamiento.map((eq, idx) => (
                    <div
                        key={idx}
                        className="flex items-center gap-2.5 text-xs font-extrabold text-foreground"
                    >
                        <div className="flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-3.5" />
                        </div>
                        <span>{eq}</span>
                    </div>
                ))}
            </div>
        </Card>
    );
};

export default EquipamientoEspacio;
