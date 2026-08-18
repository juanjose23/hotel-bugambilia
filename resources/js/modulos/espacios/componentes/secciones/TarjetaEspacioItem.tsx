import { Link } from '@inertiajs/react';
import {
    MapPin,
    Users,
    Eye,
    ArrowRight,
    CheckCircle2,
    Building2,
    Star,
    Heart,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { PropiedadesTarjetaEspacio } from '../../interfaces/espacioInterfaces';

export const TarjetaEspacioItem = ({
    espacio,
    onVerGaleria,
}: PropiedadesTarjetaEspacio) => {
    const [esFavorito, setEsFavorito] = useState(false);
    const [indiceImagen, setIndiceImagen] = useState(0);

    const rawPrice =
        espacio.precio ??
        espacio.precio_desde ??
        espacio.precio_hora ??
        espacio.precio_base ??
        0;
    const precio =
        typeof rawPrice === 'string'
            ? parseFloat(rawPrice) || 0
            : Number(rawPrice) || 0;
    const moneda = espacio.moneda || '$';
    const imagenes =
        espacio.imagenes && espacio.imagenes.length > 0
            ? espacio.imagenes
            : ['/images/terrace.webp'];

    const anteriorImagen = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIndiceImagen(
            (prev) => (prev - 1 + imagenes.length) % imagenes.length,
        );
    };

    const siguienteImagen = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIndiceImagen((prev) => (prev + 1) % imagenes.length);
    };

    return (
        <Card className="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl border border-border/60 bg-card p-0 font-sans shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-bugambilia-500/50 hover:shadow-xl">
            {/* Cabecera Estilo Airbnb con Carrusel en Móvil y Escritorio */}
            <div className="relative aspect-16/10 w-full overflow-hidden bg-muted">
                <img
                    src={imagenes[indiceImagen] || imagenes[0]}
                    alt={espacio.nombre}
                    loading="lazy"
                    className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/10 opacity-60" />

                {/* Controles de Carrusel (Flechas de navegación) */}
                {imagenes.length > 1 && (
                    <>
                        <button
                            type="button"
                            onClick={anteriorImagen}
                            className="absolute top-1/2 left-2 z-20 flex size-7 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-md transition-all hover:scale-110 hover:bg-black/70 active:scale-95"
                            title="Imagen anterior"
                        >
                            <ChevronLeft className="size-4" />
                        </button>
                        <button
                            type="button"
                            onClick={siguienteImagen}
                            className="absolute top-1/2 right-2 z-20 flex size-7 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-md transition-all hover:scale-110 hover:bg-black/70 active:scale-95"
                            title="Siguiente imagen"
                        >
                            <ChevronRight className="size-4" />
                        </button>

                        {/* Puntos Indicadores del Carrusel */}
                        <div className="absolute bottom-2 left-1/2 z-20 flex -translate-x-1/2 items-center gap-1">
                            {imagenes.map((_, idx) => (
                                <span
                                    key={idx}
                                    className={`size-1.5 rounded-full transition-all ${
                                        idx === indiceImagen
                                            ? 'w-4 bg-white'
                                            : 'bg-white/50'
                                    }`}
                                />
                            ))}
                        </div>
                    </>
                )}

                {/* Tipo Badge Airbnb */}
                <div className="absolute top-3 left-3 z-10">
                    <Badge
                        variant="outline"
                        className="rounded-full border-white/20 bg-black/40 px-3 py-0.5 text-xs font-bold text-white backdrop-blur-md"
                    >
                        <Building2
                            className="mr-1 size-3 text-bugambilia-300"
                            data-icon="inline-start"
                        />
                        {espacio.tipo_label || espacio.tipo}
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

                {/* Capacidad y Ubicación Badges */}
                <div className="absolute bottom-3 left-3 z-10 flex flex-wrap items-center gap-1.5">
                    <Badge
                        variant="outline"
                        className="flex items-center gap-1 rounded-full border-white/30 bg-black/40 px-3 py-0.5 text-xs font-extrabold text-white backdrop-blur-md"
                    >
                        <Users className="size-3 text-bugambilia-300" />
                        <span>Hasta {espacio.capacidad} pers.</span>
                    </Badge>

                    {espacio.ubicacion && (
                        <Badge
                            variant="outline"
                            className="flex items-center gap-1 rounded-full border-white/30 bg-black/40 px-3 py-0.5 text-xs font-extrabold text-white backdrop-blur-md"
                        >
                            <MapPin className="size-3 text-bugambilia-300" />
                            <span className="max-w-[120px] truncate">
                                {espacio.ubicacion}
                            </span>
                        </Badge>
                    )}
                </div>

                {/* Botón Ver Galería */}
                {onVerGaleria && imagenes.length > 1 && (
                    <button
                        type="button"
                        onClick={() => onVerGaleria(espacio)}
                        className="absolute right-3 bottom-3 z-10 flex cursor-pointer items-center gap-1 rounded-full border border-white/30 bg-black/50 px-2.5 py-0.5 text-[11px] font-extrabold text-white backdrop-blur-md hover:bg-black/70"
                    >
                        <Eye className="size-3" />
                        <span>{imagenes.length} fotos</span>
                    </button>
                )}
            </div>

            {/* Contenido: Nombre, Descripción Completa & Equipamiento */}
            <div className="flex flex-grow flex-col justify-between gap-3 p-5">
                <div className="flex flex-col gap-2">
                    <div className="flex items-start justify-between gap-2">
                        <h3 className="text-base font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                            {espacio.nombre}
                        </h3>
                        <div className="flex shrink-0 items-center gap-1 text-xs font-bold text-foreground">
                            <Star className="size-3.5 fill-amber-500 text-amber-500" />
                            <span>5.0</span>
                        </div>
                    </div>

                    {/* Descripción Completa y Legible (Sin truncar) */}
                    {espacio.descripcion && (
                        <p className="text-xs leading-relaxed font-medium text-muted-foreground">
                            {espacio.descripcion}
                        </p>
                    )}
                </div>

                <div className="mt-auto flex flex-col gap-3 pt-2">
                    {/* Equipamiento Incluido */}
                    {espacio.meta_datos?.equipamiento_incluido && (
                        <div className="flex flex-wrap items-center gap-1.5 border-t border-border/40 pt-2.5 text-[11px] font-semibold text-muted-foreground">
                            {espacio.meta_datos.equipamiento_incluido
                                .slice(0, 3)
                                .map((eq, idx) => (
                                    <span
                                        key={idx}
                                        className="inline-flex items-center gap-1 rounded-lg bg-muted/60 px-2 py-0.5 text-[11px] font-bold text-foreground"
                                    >
                                        <CheckCircle2 className="size-3 text-emerald-500" />
                                        {eq}
                                    </span>
                                ))}
                        </div>
                    )}

                    {/* Precio y Enlace estilo Airbnb */}
                    <div className="flex items-center justify-between border-t border-border/50 pt-2">
                        <div>
                            <span className="text-lg font-black text-foreground">
                                {precio > 0
                                    ? `${moneda}${formatearNumero(precio)}`
                                    : 'Consultar'}
                            </span>
                            {precio > 0 && (
                                <span className="text-xs font-semibold text-muted-foreground">
                                    {' '}
                                    / evento
                                </span>
                            )}
                        </div>

                        <Link
                            href={
                                espacio.es_restaurante ||
                                espacio.tipo
                                    ?.toLowerCase()
                                    .includes('restaurante')
                                    ? '/restaurante'
                                    : `/espacios/${espacio.slug || espacio.id}`
                            }
                            prefetch
                            className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-4 py-2 text-xs font-extrabold text-white shadow-xs transition-all hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:hover:bg-bugambilia-600"
                        >
                            {espacio.es_restaurante ||
                            espacio.tipo?.toLowerCase().includes('restaurante')
                                ? 'Ver Restaurante'
                                : 'Ver espacio'}{' '}
                            <ArrowRight className="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </Card>
    );
};

export default TarjetaEspacioItem;
