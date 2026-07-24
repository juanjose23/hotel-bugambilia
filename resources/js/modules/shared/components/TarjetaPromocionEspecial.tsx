import { Link } from '@inertiajs/react';
import {
    Gift,
    Ticket,
    CheckCircle2,
    ArrowRight,
    Tag,
    Clock,
    Copy,
    Check,
} from 'lucide-react';
import React from 'react';
import type { PromocionItem } from '@/modules/home/components/SeccionPromociones';
import { Button } from '@/modules/shared/ui/boton';

interface PropiedadesTarjetaPromocionEspecial {
    promocion: PromocionItem;
    alVerDetalles: (promo: PromocionItem) => void;
    className?: string;
}

const formatearMonto = (val?: number | null) => {
    if (val === undefined || val === null) {
        return '0.00';
    }

    return Number(val).toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const TarjetaPromocionEspecial: React.FC<
    PropiedadesTarjetaPromocionEspecial
> = ({ promocion, alVerDetalles, className = '' }) => {
    const [copiado, setCopiado] = React.useState(false);

    const simboloMoneda = promocion.moneda || '$';
    const esPaquete =
        promocion.precio_paquete !== null &&
        promocion.precio_paquete !== undefined &&
        promocion.precio_paquete > 0;
    const precioMostrar = promocion.precio_final ?? promocion.precio_paquete;
    const precioBase = promocion.precio_paquete;
    const imagenPath = promocion.imagen || '/images/hero-main.webp';

    // Cálculo de ahorro si existe precio base y precio final
    const montoAhorro =
        precioBase && precioMostrar && precioBase > precioMostrar
            ? precioBase - precioMostrar
            : null;

    // URL directa para reservar basándose en la promoción
    const urlReserva =
        promocion.url_reserva ||
        (promocion.habitacion_slug
            ? `/habitaciones/${promocion.habitacion_slug}/reservar?promo=${promocion.codigo}`
            : `/habitaciones?promo=${promocion.codigo}`);

    const copiarCodigo = (e: React.MouseEvent) => {
        e.stopPropagation();
        navigator.clipboard.writeText(promocion.codigo);
        setCopiado(true);
        setTimeout(() => setCopiado(false), 2000);
    };

    return (
        <article
            className={`group shadow-airbnb relative flex flex-col justify-between overflow-hidden rounded-3xl border border-amber-500/40 bg-gradient-to-b from-card via-card to-amber-950/20 font-sans shadow-xl shadow-amber-500/5 transition-all duration-500 hover:-translate-y-2 hover:border-amber-400 hover:shadow-2xl hover:shadow-amber-500/20 dark:border-amber-500/30 ${className}`}
        >
            <div>
                {/* Banner de Imagen Principal con Gradientes Luxury */}
                <div className="relative h-56 overflow-hidden bg-muted">
                    <img
                        src={imagenPath}
                        alt={promocion.nombre}
                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-black/30" />

                    {/* Listón Flotante Superior Izquierdo */}
                    <div className="absolute top-3 left-3 flex items-center gap-2">
                        <span className="shadow-airbnb flex items-center gap-1.5 rounded-full border border-amber-300/50 bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-400 px-3.5 py-1 text-[11px] font-black tracking-wider text-black uppercase">
                            {esPaquete ? (
                                <Gift className="h-3.5 w-3.5 text-black" />
                            ) : (
                                <Ticket className="h-3.5 w-3.5 text-black" />
                            )}
                            <span>
                                {promocion.badge ||
                                    (esPaquete
                                        ? 'Paquete Combo'
                                        : 'Código Promocional')}
                            </span>
                        </span>
                    </div>

                    {/* Insignia Oferta Limitada Superior Derecho */}
                    <div className="absolute top-3 right-3">
                        <span className="flex items-center gap-1 rounded-full border border-white/20 bg-black/70 px-3 py-1 text-[10px] font-extrabold tracking-wider text-amber-300 uppercase backdrop-blur-md">
                            <Clock className="h-3 w-3 text-amber-400" />
                            <span>
                                {promocion.valido_hasta || 'Cupos Limitados'}
                            </span>
                        </span>
                    </div>

                    {/* Título de la Promoción sobre Imagen */}
                    <div className="absolute right-4 bottom-3 left-4 flex items-center justify-between text-white">
                        <div className="flex items-center gap-2.5">
                            <div className="flex h-9 w-9 items-center justify-center rounded-full border border-amber-400/40 bg-black/50 backdrop-blur-md">
                                <Gift className="h-4 w-4 text-amber-300" />
                            </div>
                            <h3 className="text-base font-black tracking-wide text-white drop-shadow-md sm:text-lg">
                                {promocion.nombre}
                            </h3>
                        </div>
                    </div>
                </div>

                {/* Contenido de la Tarjeta */}
                <div className="p-6">
                    {/* Tarjeta de Tarifa / Descuento Destacado */}
                    {esPaquete ? (
                        <div className="mb-5 flex items-center justify-between rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 backdrop-blur-md">
                            <div>
                                <span className="block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                                    Precio Especial Todo Incluido
                                </span>
                                <div className="flex items-baseline gap-2">
                                    <span className="text-2xl font-black text-amber-600 dark:text-amber-400">
                                        {simboloMoneda}{' '}
                                        {formatearMonto(precioMostrar)}
                                    </span>
                                    {precioBase &&
                                        precioBase > (precioMostrar || 0) && (
                                            <span className="text-xs font-bold text-muted-foreground line-through">
                                                {simboloMoneda}{' '}
                                                {formatearMonto(precioBase)}
                                            </span>
                                        )}
                                </div>
                            </div>

                            {/* Badge de Ahorro */}
                            {montoAhorro ? (
                                <span className="flex flex-col items-end rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 px-3 py-1.5 text-center text-white shadow-md">
                                    <span className="text-[9px] font-bold tracking-wider uppercase">
                                        Ahorra
                                    </span>
                                    <span className="text-xs font-black">
                                        {simboloMoneda}{' '}
                                        {formatearMonto(montoAhorro)}
                                    </span>
                                </span>
                            ) : (
                                promocion.descuento_porcentaje && (
                                    <span className="rounded-xl bg-rose-600 px-3.5 py-1.5 text-xs font-black text-white shadow-md">
                                        {promocion.descuento_porcentaje}% OFF
                                    </span>
                                )
                            )}
                        </div>
                    ) : (
                        <div className="mb-5 flex items-center justify-between rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-4">
                            <div>
                                <span className="block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                                    Cupón Exclusivo de Descuento
                                </span>
                                <div className="font-mono text-base font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                    {promocion.codigo}
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={copiarCodigo}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-bugambilia-500/30 bg-card px-3 py-1.5 text-xs font-bold text-bugambilia-600 transition hover:bg-muted dark:text-bugambilia-400"
                            >
                                {copiado ? (
                                    <>
                                        <Check className="h-3.5 w-3.5 text-emerald-500" />
                                        <span>Copiado</span>
                                    </>
                                ) : (
                                    <>
                                        <Copy className="h-3.5 w-3.5" />
                                        <span>Copiar</span>
                                    </>
                                )}
                            </button>
                        </div>
                    )}

                    {/* Descripción Corta */}
                    <p className="mb-5 line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground">
                        {promocion.descripcion}
                    </p>

                    {/* Lista de Inclusiones del Combo con Checkmarks Dorados */}
                    {promocion.itemsIncluidos &&
                        promocion.itemsIncluidos.length > 0 && (
                            <div className="mb-6 space-y-2.5 rounded-2xl border border-border/60 bg-background/60 p-4">
                                <span className="flex items-center gap-1.5 text-[10px] font-extrabold tracking-widest text-foreground uppercase">
                                    <Tag className="h-3 w-3 text-amber-500" />
                                    Incluido en esta Oferta:
                                </span>
                                {promocion.itemsIncluidos
                                    .slice(0, 3)
                                    .map((item, idx) => (
                                        <div
                                            key={idx}
                                            className="flex items-center gap-2 text-xs font-semibold text-foreground/90"
                                        >
                                            <CheckCircle2 className="h-4 w-4 shrink-0 text-amber-500" />
                                            <span className="truncate">
                                                {item}
                                            </span>
                                        </div>
                                    ))}
                            </div>
                        )}
                </div>
            </div>

            {/* Footer con Acciones Directas de Reserva */}
            <div className="space-y-2.5 border-t border-border/40 px-6 py-5">
                <Button
                    asChild
                    className="w-full cursor-pointer rounded-2xl bg-gradient-to-r from-bugambilia-600 via-bugambilia-600 to-amber-600 py-3.5 text-xs font-black tracking-wider text-white uppercase shadow-lg shadow-bugambilia-600/20 transition-all hover:scale-[1.02] hover:bg-bugambilia-700"
                >
                    <Link href={urlReserva}>
                        <span>Reservar con esta Promoción</span>
                        <ArrowRight className="ml-2 h-4 w-4" />
                    </Link>
                </Button>

                <button
                    type="button"
                    onClick={() => alVerDetalles(promocion)}
                    className="inline-flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-border/80 bg-background py-2 text-xs font-bold text-muted-foreground transition-all hover:bg-muted hover:text-foreground"
                >
                    <span>Ver Ficha Completa & Condiciones</span>
                </button>
            </div>
        </article>
    );
};
