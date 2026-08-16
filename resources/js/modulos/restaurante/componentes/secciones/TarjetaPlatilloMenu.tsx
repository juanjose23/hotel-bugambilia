import { Clock, Flame } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { PropiedadesTarjetaPlatilloMenu } from '../../interfaces/restauranteInterfaces';

export const TarjetaPlatilloMenu = ({
    item,
}: PropiedadesTarjetaPlatilloMenu) => {
    return (
        <Card className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-card/90 font-sans shadow-sm backdrop-blur-xs transition-all duration-300 hover:-translate-y-1.5 hover:border-amber-500/40 hover:shadow-xl">
            {/* Imagen del Platillo */}
            {item.imagen ? (
                <div className="relative aspect-16/10 w-full overflow-hidden bg-muted">
                    <img
                        src={item.imagen}
                        alt={item.nombre}
                        loading="lazy"
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60" />

                    <div className="absolute top-3 left-3 z-10">
                        <Badge
                            variant="outline"
                            className="rounded-full border-white/20 bg-black/40 px-3 py-0.5 text-xs font-bold text-white backdrop-blur-md"
                        >
                            {item.categoria}
                        </Badge>
                    </div>
                </div>
            ) : null}

            {/* Información del Platillo */}
            <CardContent className="flex flex-grow flex-col gap-3 p-6">
                <div className="flex items-start justify-between gap-2">
                    <h3 className="text-lg font-black text-foreground transition-colors group-hover:text-amber-500">
                        {item.nombre}
                    </h3>
                    {item.precio !== null && (
                        <div className="shrink-0 text-right">
                            <span className="text-lg font-black text-amber-500">
                                {item.moneda || '$'}
                                {formatearNumero(item.precio)}
                            </span>
                        </div>
                    )}
                </div>

                <p className="line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground">
                    {item.descripcion}
                </p>

                {/* Meta datos: Tiempo & Etiquetas */}
                <div className="flex flex-wrap items-center gap-2 border-t border-border/40 pt-2 text-xs">
                    {item.tiempo_preparacion && (
                        <span className="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-[11px] font-bold text-muted-foreground">
                            <Clock className="size-3 text-amber-500" />
                            {item.tiempo_preparacion}
                        </span>
                    )}

                    {item.etiquetas &&
                        item.etiquetas.map((etiqueta, idx) => (
                            <Badge
                                key={idx}
                                variant="secondary"
                                className="rounded-full border-amber-500/20 bg-amber-500/10 text-[10px] font-extrabold text-amber-600 dark:text-amber-400"
                            >
                                <Flame
                                    className="mr-1 size-3 text-amber-500"
                                    data-icon="inline-start"
                                />
                                {etiqueta}
                            </Badge>
                        ))}
                </div>
            </CardContent>
        </Card>
    );
};
