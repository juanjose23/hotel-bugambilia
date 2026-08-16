import { Star, Quote } from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { TestimonioInicio } from '../../interfaces/inicio';

interface PropiedadesTarjetaTestimonioItem {
    testimonio: TestimonioInicio;
}

export const TarjetaTestimonioItem = ({
    testimonio,
}: PropiedadesTarjetaTestimonioItem) => {
    return (
        <Card className="rounded-3xl border-border/80 bg-card p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <CardContent className="flex h-full flex-col justify-between gap-4 p-0">
                <div className="flex flex-col gap-3">
                    <div className="flex items-center justify-between">
                        <div className="flex gap-1 text-amber-500">
                            {Array.from({
                                length: testimonio.calificacion,
                            }).map((_, i) => (
                                <Star
                                    key={i}
                                    className="size-4 fill-amber-500"
                                />
                            ))}
                        </div>
                        <Quote className="size-6 text-muted-foreground/30" />
                    </div>

                    <p className="text-xs leading-relaxed text-muted-foreground italic sm:text-sm">
                        "{testimonio.comentario}"
                    </p>
                </div>

                <div className="flex items-center gap-3 border-t border-border/50 pt-2">
                    <div className="size-10 overflow-hidden rounded-full border border-border bg-muted">
                        {testimonio.avatarAutor ? (
                            <img
                                src={testimonio.avatarAutor}
                                alt={testimonio.nombreAutor}
                                className="h-full w-full object-cover"
                            />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-xs font-bold text-primary">
                                {testimonio.nombreAutor.charAt(0)}
                            </div>
                        )}
                    </div>
                    <div>
                        <h4 className="text-xs font-extrabold text-foreground">
                            {testimonio.nombreAutor}
                        </h4>
                        <p className="text-[11px] text-muted-foreground">
                            {testimonio.paisAutor} • {testimonio.tipoEstancia}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
};
