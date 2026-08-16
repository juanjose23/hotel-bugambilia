import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesTarjetaValorItem {
    titulo: string;
    descripcion: string;
    Icono: LucideIcon;
}

export function TarjetaValorItem({
    titulo,
    descripcion,
    Icono,
}: PropiedadesTarjetaValorItem) {
    return (
        <Card className="flex flex-col items-center rounded-3xl border-border/80 bg-background p-8 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <CardContent className="flex flex-col items-center p-0">
                <div className="mb-6 flex size-14 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                    <Icono className="size-7 text-bugambilia-600 dark:text-bugambilia-400" />
                </div>
                <h3 className="mb-2 text-lg font-extrabold text-foreground">
                    {titulo}
                </h3>
                <p className="text-xs leading-relaxed text-muted-foreground">
                    {descripcion}
                </p>
            </CardContent>
        </Card>
    );
}
