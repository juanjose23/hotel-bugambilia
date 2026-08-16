import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesTarjetaBloqueContacto {
    Icono: LucideIcon;
    titulo: string;
    lineas: string[];
}

export function TarjetaBloqueContacto({
    Icono,
    titulo,
    lineas,
}: PropiedadesTarjetaBloqueContacto) {
    return (
        <Card className="rounded-3xl border-border/80 bg-card p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
            <CardContent className="flex items-start gap-4 p-0">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                    <Icono className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
                </div>
                <div>
                    <h3 className="mb-1 text-xs font-extrabold tracking-wider text-foreground uppercase">
                        {titulo}
                    </h3>
                    {lineas.map((linea, index) => (
                        <p
                            key={index}
                            className="text-xs font-medium text-muted-foreground"
                        >
                            {linea}
                        </p>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
