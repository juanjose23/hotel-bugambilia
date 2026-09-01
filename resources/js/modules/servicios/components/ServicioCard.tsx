import { Link } from '@inertiajs/react';
import { ArrowRight, Tag, ConciergeBell } from 'lucide-react';
import type { ServicioItem } from '../types';

interface PropsServicioCard {
    servicio: ServicioItem;
}

export const ServicioCard = ({ servicio }: PropsServicioCard) => {
    const enlaceDetalle = servicio.slug
        ? `/servicios/${servicio.slug}`
        : `/servicios/${servicio.id}`;

    return (
        <div className="group flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-xl dark:hover:border-rose-500/40">
            {/* Imagen con Aspect Ratio 16:9 */}
            <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
                <img
                    src={servicio.imagen || '/images/service-kitchen.webp'}
                    alt={servicio.nombre}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                {servicio.categoria && (
                    <span className="absolute top-3.5 left-3.5 rounded-full border border-white/20 bg-background/90 px-3 py-0.5 text-[11px] font-black text-foreground shadow-xs backdrop-blur-md">
                        {servicio.categoria}
                    </span>
                )}

                {servicio.precio !== undefined &&
                servicio.precio !== null &&
                Number(servicio.precio) > 0 ? (
                    <span className="absolute right-3.5 bottom-3.5 flex items-center gap-1 rounded-full border border-white/20 bg-foreground/90 px-2.5 py-0.5 text-xs font-black text-background shadow-xs backdrop-blur-md">
                        <Tag className="size-3" />
                        <span>
                            {servicio.moneda || '$'}
                            {Number(servicio.precio).toFixed(2)}
                        </span>
                    </span>
                ) : (
                    <span className="absolute right-3.5 bottom-3.5 flex items-center gap-1 rounded-full border border-white/20 bg-primary/90 px-2.5 py-0.5 text-xs font-black text-primary-foreground shadow-xs backdrop-blur-md">
                        <ConciergeBell className="size-3" />
                        <span>Servicio Exclusivo</span>
                    </span>
                )}
            </div>

            {/* Contenido */}
            <div className="flex flex-1 flex-col justify-between p-6">
                <div>
                    <h3 className="text-lg font-black tracking-tight text-foreground transition-colors group-hover:text-primary sm:text-xl dark:group-hover:text-rose-400">
                        {servicio.nombre}
                    </h3>
                    <p className="mt-2 line-clamp-3 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                        {servicio.descripcion ||
                            'Disfruta de nuestras exclusivas amenidades y servicios diseñados para una estancia insuperable en Estelí.'}
                    </p>
                </div>

                <div className="mt-6 flex items-center justify-between border-t border-border/60 pt-4">
                    <span className="text-xs font-bold text-muted-foreground">
                        Hotel Bugambilias
                    </span>
                    <Link
                        href={enlaceDetalle}
                        className="inline-flex items-center gap-1.5 text-xs font-black text-primary hover:underline dark:text-rose-400"
                    >
                        <span>Ver detalles</span>
                        <ArrowRight className="size-3.5 transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default ServicioCard;
