import { Layers } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import type { SubEspacioItem } from '../../interfaces/espacioInterfaces';

interface PropiedadesListadoSubEspacios {
    subEspacios: SubEspacioItem[];
}

export const ListadoSubEspacios = ({
    subEspacios = [],
}: PropiedadesListadoSubEspacios) => {
    if (subEspacios.length === 0) {
        return null;
    }

    return (
        <Card className="rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xs">
            <div className="mb-4 flex items-center gap-2">
                <Layers className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
                <h3 className="text-base font-black text-foreground">
                    Ambientes & Sub-Espacios Integrados
                </h3>
            </div>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {subEspacios.map((sub) => (
                    <div
                        key={sub.id}
                        className="flex items-center justify-between rounded-2xl border border-border/60 bg-muted/30 p-3.5"
                    >
                        <div>
                            <span className="block text-xs font-extrabold text-foreground">
                                {sub.nombre}
                            </span>
                            <span className="text-[11px] font-medium text-muted-foreground">
                                Capacidad: {sub.capacidad} personas
                            </span>
                        </div>
                        <Badge
                            variant="outline"
                            className="rounded-full border-emerald-500/30 bg-emerald-500/10 text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400"
                        >
                            Disponible
                        </Badge>
                    </div>
                ))}
            </div>
        </Card>
    );
};

export default ListadoSubEspacios;
