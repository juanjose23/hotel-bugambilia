import { Card } from '@/modulos/compartido/ui/tarjeta';
import type { PropiedadesTarjetaEstadisticaItem } from '../../interfaces/acercaDeInterfaces';

export const TarjetaEstadisticaItem = ({
    valor,
    etiqueta,
    Icono,
    icono,
}: PropiedadesTarjetaEstadisticaItem & { icono?: React.ElementType }) => {
    const ComponenteIcono = Icono || icono;

    return (
        <Card className="flex flex-col items-center justify-center rounded-2xl border border-border/70 bg-card p-4 text-center font-sans shadow-xs transition-all duration-300 hover:border-bugambilia-500/50 hover:shadow-md">
            {ComponenteIcono && (
                <div className="mb-2 flex size-10 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                    <ComponenteIcono className="size-5" />
                </div>
            )}
            <span className="text-xl font-black text-foreground">{valor}</span>
            <span className="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                {etiqueta}
            </span>
        </Card>
    );
};
