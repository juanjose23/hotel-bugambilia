import { Box } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesEquipamientoHabitacion {
    equipamiento?: string[];
}

export const EquipamientoHabitacion = ({
    equipamiento = [],
}: PropiedadesEquipamientoHabitacion) => {
    if (!equipamiento || equipamiento.length === 0) {
        return null;
    }

    return (
        <section className="border-t border-border/40 bg-card/40 py-10 font-sans">
            <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <h3 className="mb-4 flex items-center gap-2 text-lg font-extrabold tracking-tight text-foreground">
                    <Box className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
                    Equipamiento de la Habitación
                </h3>

                <div className="flex flex-wrap gap-2">
                    {equipamiento.map((item, idx) => (
                        <Badge
                            key={idx}
                            variant="outline"
                            className="rounded-full border border-border/80 bg-card px-3.5 py-1.5 text-xs font-semibold text-foreground"
                        >
                            {item}
                        </Badge>
                    ))}
                </div>
            </div>
        </section>
    );
};
