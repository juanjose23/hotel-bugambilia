import { Link } from '@inertiajs/react';
import {
    Gift,
    ArrowRight,
    Sparkles,
    CheckCircle2,
    Ticket,
    Tag,
} from 'lucide-react';

export interface PromocionItem {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    badge?: string;
    precio_paquete?: number | null;
    precio_final?: number | null;
    descuento_porcentaje?: number | null;
    descuento_monto?: number | null;
    moneda?: string;
    imagen?: string | null;
    itemsIncluidos?: string[];
}

interface PromotionsSectionProps {
    promociones?: PromocionItem[];
}

export default function PromotionsSection({
    promociones = [],
}: PromotionsSectionProps) {
    if (promociones.length === 0) {
        return null;
    }

    return (
        <section className="border-b border-border/40 bg-card py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <div className="max-w-2xl">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-600 uppercase dark:text-amber-400">
                            <Sparkles className="h-3.5 w-3.5" />
                            Ofertas, Paquetes & Cupones
                        </div>
                        <h2 className="text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Promociones{' '}
                            <span className="font-serif font-normal text-amber-500 italic">
                                Especiales
                            </span>
                        </h2>
                        <p className="mt-2 text-base font-medium text-muted-foreground sm:text-lg">
                            Aproveche nuestros paquetes todo incluido o aplique
                            códigos de descuento exclusivos en sus próximas
                            reservas.
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
                    {promociones.map((promo) => {
                        const simboloMoneda = promo.moneda || '$';
                        const esPaquete =
                            promo.precio_paquete !== null &&
                            promo.precio_paquete !== undefined &&
                            promo.precio_paquete > 0;
                        const precioMostrar =
                            promo.precio_final ?? promo.precio_paquete;
                        const precioBase = promo.precio_paquete;
                        const imagenPath =
                            promo.imagen || '/images/hero-main.jpg';

                        return (
                            <div
                                key={promo.id}
                                className="group shadow-airbnb hover:shadow-airbnb-hover relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/70 bg-background transition-all duration-300 hover:-translate-y-1"
                            >
                                {/* Image banner */}
                                <div className="relative h-48 overflow-hidden bg-muted">
                                    <img
                                        src={imagenPath}
                                        alt={promo.nombre}
                                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent" />
                                    <div className="absolute top-3 left-3">
                                        <span className="flex items-center gap-1 rounded-full bg-amber-400 px-3 py-1 text-[10px] font-extrabold tracking-wider text-black uppercase shadow-sm">
                                            {esPaquete ? (
                                                <Gift className="h-3 w-3" />
                                            ) : (
                                                <Ticket className="h-3 w-3" />
                                            )}
                                            <span>
                                                {promo.badge ||
                                                    (esPaquete
                                                        ? 'Paquete Combo'
                                                        : 'Código Descuento')}
                                            </span>
                                        </span>
                                    </div>
                                    <div className="absolute right-4 bottom-3 left-4 flex items-center justify-between text-white">
                                        <div className="flex items-center gap-2">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-md">
                                                {esPaquete ? (
                                                    <Gift className="h-4 w-4 text-white" />
                                                ) : (
                                                    <Tag className="h-4 w-4 text-white" />
                                                )}
                                            </div>
                                            <span className="text-xs font-black tracking-wider uppercase">
                                                {promo.nombre}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Body Content */}
                                <div className="flex flex-grow flex-col justify-between p-6">
                                    <div>
                                        {/* Mode 1: Paquete Completo con precio global */}
                                        {esPaquete ? (
                                            <div className="mb-3 flex items-baseline justify-between rounded-2xl border border-amber-500/20 bg-amber-500/10 p-3">
                                                <div>
                                                    <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                                        Precio Total Paquete
                                                    </span>
                                                    <div className="text-2xl font-black text-amber-600 dark:text-amber-400">
                                                        {simboloMoneda}
                                                        {precioMostrar
                                                            ? precioMostrar.toFixed(
                                                                  2,
                                                              )
                                                            : '0.00'}{' '}
                                                        <span className="text-xs font-semibold text-muted-foreground">
                                                            {' '}
                                                            {simboloMoneda}
                                                        </span>
                                                    </div>
                                                </div>
                                                {promo.descuento_porcentaje ? (
                                                    <span className="rounded-full bg-rose-500 px-2.5 py-1 text-xs font-black text-white">
                                                        {
                                                            promo.descuento_porcentaje
                                                        }
                                                        % Descuento
                                                    </span>
                                                ) : (
                                                    precioBase &&
                                                    precioBase >
                                                        (precioMostrar ||
                                                            0) && (
                                                        <span className="text-xs font-bold text-muted-foreground line-through">
                                                            {simboloMoneda}
                                                            {precioBase.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                    )
                                                )}
                                            </div>
                                        ) : (
                                            /* Mode 2: Solo Código / Cupón de Descuento */
                                            <div className="mb-3 flex items-center justify-between rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 p-3">
                                                <div>
                                                    <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                                        Código Promocional
                                                    </span>
                                                    <div className="font-mono text-base font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                                        {promo.codigo}
                                                    </div>
                                                </div>
                                                {promo.descuento_porcentaje ? (
                                                    <span className="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-black text-white shadow-sm">
                                                        {
                                                            promo.descuento_porcentaje
                                                        }
                                                        % Descuento
                                                    </span>
                                                ) : promo.descuento_monto ? (
                                                    <span className="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-black text-white shadow-sm">
                                                        C${' '}
                                                        {promo.descuento_monto}{' '}
                                                        Descuento
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-amber-500 px-2.5 py-1 text-xs font-black text-black">
                                                        Cupón Especial
                                                    </span>
                                                )}
                                            </div>
                                        )}

                                        <p className="mb-4 text-xs leading-relaxed font-medium text-muted-foreground">
                                            {promo.descripcion}
                                        </p>

                                        {/* Ítems Incluidos en el Paquete si existen */}
                                        {promo.itemsIncluidos &&
                                            promo.itemsIncluidos.length > 0 && (
                                                <div className="mb-6 space-y-1.5">
                                                    <span className="mb-1 block text-[10px] font-extrabold tracking-wider text-foreground uppercase">
                                                        Incluido en este Combo:
                                                    </span>
                                                    {promo.itemsIncluidos.map(
                                                        (item, itemIdx) => (
                                                            <div
                                                                key={itemIdx}
                                                                className="flex items-center gap-2 text-xs font-semibold text-foreground/90"
                                                            >
                                                                <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                                                <span>
                                                                    {item}
                                                                </span>
                                                            </div>
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                    </div>

                                    <div className="border-t border-border/40 pt-4">
                                        <Link
                                            href="/habitaciones"
                                            className="shadow-airbnb hover:shadow-airbnb-hover group/btn inline-flex w-full items-center justify-between rounded-xl bg-bugambilia-600 px-4 py-3 text-xs font-black tracking-wider text-white uppercase transition-all duration-300 hover:bg-bugambilia-700"
                                        >
                                            <span>
                                                {esPaquete
                                                    ? 'Reservar Paquete Completo'
                                                    : `Usar Código ${promo.codigo}`}
                                            </span>
                                            <ArrowRight className="h-4 w-4 transition-transform group-hover/btn:translate-x-1" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
