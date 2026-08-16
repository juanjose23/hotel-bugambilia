import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Star,
    Heart,
    Sparkles,
    CheckCircle2,
    ShieldCheck,
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import { resolverImagenStorage } from '@/modulos/compartido/utilidades/imagenes';
import type { PropiedadesTarjetaServicioItem } from '../../interfaces/servicioInterfaces';

export const TarjetaServicioItem = ({
    servicio,
}: PropiedadesTarjetaServicioItem) => {
    const [esFavorito, setEsFavorito] = useState(false);
    const rawPrice = servicio.precio ?? servicio.precio_base;
    const precio =
        rawPrice !== null && rawPrice !== undefined ? Number(rawPrice) : null;
    const moneda = servicio.moneda || '$';
    const imagen = resolverImagenStorage(
        servicio.imagen,
        '/images/terrace.webp',
    );
    const categoria = servicio.categoria || 'Servicio Boutique';
    const descripcion =
        servicio.descripcion ||
        'Servicio exclusivo disponible para complementar su estancia con el máximo confort en Estelí.';

    return (
        <Card className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/60 bg-card p-0 font-sans shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/40 hover:shadow-xl">
            {/* Cabecera Estilo Airbnb (Aspect Ratio 16/10) */}
            <div className="relative aspect-16/10 w-full overflow-hidden bg-muted">
                <img
                    src={imagen}
                    alt={servicio.nombre}
                    loading="lazy"
                    className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10 opacity-60" />

                {/* Categoría Badge Airbnb */}
                <div className="absolute top-3 left-3 z-10">
                    <Badge
                        variant="outline"
                        className="rounded-full border-white/20 bg-black/40 px-3 py-0.5 text-xs font-bold text-white backdrop-blur-md"
                    >
                        {categoria}
                    </Badge>
                </div>

                {/* Botón Favorito Airbnb */}
                <button
                    type="button"
                    onClick={(e) => {
                        e.preventDefault();
                        setEsFavorito(!esFavorito);
                    }}
                    className="absolute top-3 right-3 z-10 flex size-8 cursor-pointer items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-md transition-transform hover:bg-black/50 active:scale-90"
                >
                    <Heart
                        className={`size-4 ${esFavorito ? 'fill-rose-500 text-rose-500' : 'text-white'}`}
                    />
                </button>

                {/* Badge Servicio Destacado / Garantía */}
                <div className="absolute bottom-3 left-3 z-10">
                    <Badge
                        variant="outline"
                        className="flex items-center gap-1 rounded-full border-white/30 bg-black/40 px-3 py-0.5 text-xs font-extrabold text-white backdrop-blur-md"
                    >
                        <Sparkles className="size-3 text-bugambilia-300" />
                        <span>
                            {servicio.destacado
                                ? 'Popular & Exclusivo'
                                : 'Atención 24/7'}
                        </span>
                    </Badge>
                </div>
            </div>

            {/* Contenido: Nombre, Descripción Completa & Características */}
            <div className="flex flex-grow flex-col justify-between gap-3 p-5">
                <div className="flex flex-col gap-2">
                    <div className="flex items-start justify-between gap-2">
                        <h3 className="text-base font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                            {servicio.nombre}
                        </h3>
                        <div className="flex shrink-0 items-center gap-1 text-xs font-bold text-foreground">
                            <Star className="size-3.5 fill-amber-500 text-amber-500" />
                            <span>5.0</span>
                        </div>
                    </div>

                    {/* Descripción Legible (Sin truncar) */}
                    <p className="text-xs leading-relaxed font-medium text-muted-foreground">
                        {descripcion}
                    </p>
                </div>

                <div className="flex flex-col gap-3 pt-2">
                    {/* Chips de Características */}
                    <div className="flex flex-wrap items-center gap-2 border-t border-border/40 pt-2.5 text-[11px] font-semibold text-muted-foreground">
                        {servicio.caracteristicas &&
                        servicio.caracteristicas.length > 0 ? (
                            servicio.caracteristicas
                                .slice(0, 3)
                                .map((carac, idx) => (
                                    <span
                                        key={idx}
                                        className="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <CheckCircle2 className="size-3" />{' '}
                                        {carac}
                                    </span>
                                ))
                        ) : (
                            <>
                                <span className="inline-flex items-center gap-1 text-bugambilia-600 dark:text-bugambilia-400">
                                    <ShieldCheck className="size-3" /> Garantía
                                    Boutique
                                </span>
                                <span>•</span>
                                <span className="inline-flex items-center gap-1 text-emerald-500">
                                    <CheckCircle2 className="size-3" />{' '}
                                    Respuesta Inmediata
                                </span>
                            </>
                        )}
                    </div>

                    {/* Precio y Botón de Acción Estilo Airbnb */}
                    <div className="flex items-center justify-between border-t border-border/50 pt-2">
                        <div>
                            {precio !== null && precio > 0 ? (
                                <div>
                                    <span className="text-lg font-black text-foreground">
                                        {moneda.length === 1 ? moneda : '$'}
                                        {formatearNumero(precio)}
                                    </span>
                                    <span className="text-xs font-semibold text-muted-foreground">
                                        {' '}
                                        / tarifa
                                    </span>
                                </div>
                            ) : (
                                <span className="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Incluido / Solicitud
                                </span>
                            )}
                        </div>

                        <Link
                            href={`/servicios/${servicio.slug || servicio.id}`}
                            prefetch
                            className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-4 py-2 text-xs font-extrabold text-white shadow-xs transition-all hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:hover:bg-bugambilia-600"
                        >
                            Ver detalles <ArrowRight className="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </Card>
    );
};

export default TarjetaServicioItem;
