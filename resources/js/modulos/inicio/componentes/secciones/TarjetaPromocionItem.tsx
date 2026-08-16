import { Link } from '@inertiajs/react';
import { Sparkles, Check, ArrowRight } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { PromocionInicio } from '../../interfaces/inicio';

interface PropiedadesTarjetaPromocionItem {
    promocion: PromocionInicio;
}

export const TarjetaPromocionItem = ({
    promocion,
}: PropiedadesTarjetaPromocionItem) => {
    return (
        <Card className="group relative overflow-hidden rounded-3xl border-border/80 bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div className="relative aspect-16/9 w-full overflow-hidden">
                <img
                    src={promocion.imagen || '/images/hero-main.webp'}
                    alt={promocion.nombre}
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                <div className="absolute top-4 left-4">
                    <Badge
                        variant="default"
                        className="bg-bugambilia-600 font-extrabold text-white"
                    >
                        <Sparkles
                            className="mr-1 size-3"
                            data-icon="inline-start"
                        />
                        {promocion.badge}
                    </Badge>
                </div>
            </div>

            <CardContent className="flex flex-col gap-4 p-6">
                <div>
                    <h3 className="mb-1 text-xl font-black text-foreground">
                        {promocion.nombre}
                    </h3>
                    <p className="text-xs leading-relaxed text-muted-foreground">
                        {promocion.descripcion}
                    </p>
                </div>

                {promocion.itemsIncluidos && (
                    <ul className="flex flex-col gap-1.5 border-t border-border/50 pt-2">
                        {promocion.itemsIncluidos.map((item, index) => (
                            <li
                                key={index}
                                className="flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Check className="size-3.5 shrink-0 text-emerald-500" />
                                <span>{item}</span>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="flex items-center justify-between pt-2">
                    {promocion.precio_final && (
                        <div>
                            <span className="block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                Precio Especial
                            </span>
                            <span className="text-xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                ${promocion.precio_final}{' '}
                                <span className="text-xs font-semibold text-muted-foreground">
                                    {promocion.moneda}
                                </span>
                            </span>
                        </div>
                    )}
                    <Button
                        asChild
                        size="sm"
                        className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                    >
                        <Link href="/habitaciones" prefetch>
                            Reservar Oferta
                            <ArrowRight
                                className="size-3.5"
                                data-icon="inline-end"
                            />
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
};
