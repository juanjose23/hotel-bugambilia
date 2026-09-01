import {
    Calendar,
    Tag,
    Gift,
    Percent,
    CheckCircle2,
    MessageCircle,
    ArrowRight,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { PromocionItem } from '../types';

interface PromocionCardProps {
    promocion: PromocionItem;
    alSeleccionar: (promo: PromocionItem) => void;
    telefonoWhatsApp?: string;
}

export const PromocionCard = ({
    promocion,
    alSeleccionar,
    telefonoWhatsApp = '50587136805',
}: PromocionCardProps) => {
    const tieneDescuento =
        (promocion.descuento_porcentaje &&
            promocion.descuento_porcentaje > 0) ||
        (promocion.descuento_monto && promocion.descuento_monto > 0);

    const textoDescuento = promocion.descuento_porcentaje
        ? `${Math.round(promocion.descuento_porcentaje)}% OFF`
        : promocion.descuento_monto
          ? `-${promocion.moneda}${Number(promocion.descuento_monto).toFixed(0)}`
          : null;

    const formatearFecha = (str?: string) => {
        if (!str) {
            return null;
        }

        try {
            const [y, m, d] = str.split('-').map(Number);

            if (!y || !m || !d) {
                return null;
            }

            const dt = new Date(y, m - 1, d);

            return dt.toLocaleDateString('es-NI', {
                day: 'numeric',
                month: 'short',
            });
        } catch {
            return null;
        }
    };

    const fechaInicioTxt = formatearFecha(promocion.fecha_inicio);
    const fechaFinTxt = formatearFecha(promocion.fecha_fin);

    const mensajeWhatsApp = encodeURIComponent(
        `¡Hola Hotel Bugambilias! Deseo reservar la promoción "${promocion.nombre}" (${promocion.codigo}) por ${promocion.moneda}${Number(promocion.precio_final).toFixed(0)}.`,
    );

    const telefonoLimpio = telefonoWhatsApp.replace(/\D/g, '');

    return (
        <div className="group flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl dark:hover:border-rose-500/50">
            {/* Cabecera con Imagen */}
            <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
                <img
                    src={promocion.imagen || '/images/hero-main.webp'}
                    alt={promocion.nombre}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />

                {/* Badge de Categoría / Tipo */}
                <span className="absolute top-3.5 left-3.5 rounded-full border border-white/20 bg-background/90 px-3 py-0.5 text-[10px] font-black text-foreground uppercase shadow-xs backdrop-blur-md">
                    {promocion.tipo}
                </span>

                {/* Badge de Descuento Destacado */}
                {textoDescuento && (
                    <span className="absolute top-3.5 right-3.5 flex animate-pulse items-center gap-1 rounded-full bg-rose-600 px-3 py-1 text-xs font-black text-white shadow-md">
                        <Percent className="size-3" />
                        <span>{textoDescuento}</span>
                    </span>
                )}

                {/* Badge de Vigencia */}
                {(fechaInicioTxt || fechaFinTxt) && (
                    <span className="absolute bottom-3.5 left-3.5 flex items-center gap-1.5 rounded-full border border-white/20 bg-black/70 px-3 py-1 text-[11px] font-bold text-white shadow-xs backdrop-blur-md">
                        <Calendar className="size-3 text-rose-300" />
                        <span>
                            {fechaInicioTxt ? `Desde ${fechaInicioTxt}` : ''}{' '}
                            {fechaFinTxt
                                ? `hasta ${fechaFinTxt}`
                                : 'Válido hoy'}
                        </span>
                    </span>
                )}
            </div>

            {/* Contenido */}
            <div className="flex flex-1 flex-col justify-between p-6">
                <div>
                    {/* Código de Cupón / Promo */}
                    <div className="flex items-center gap-1.5 text-[11px] font-black text-primary uppercase dark:text-rose-400">
                        <Tag className="size-3" />
                        <span>Código: {promocion.codigo}</span>
                    </div>

                    <h3 className="mt-1.5 text-lg font-black tracking-tight text-foreground transition-colors group-hover:text-primary sm:text-xl dark:group-hover:text-rose-400">
                        {promocion.nombre}
                    </h3>

                    <p className="mt-2 line-clamp-2 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                        {promocion.descripcion ||
                            'Aprovecha nuestras tarifas especiales con beneficios exclusivos para una estancia inolvidable.'}
                    </p>

                    {/* Beneficios Incluidos */}
                    {promocion.beneficios &&
                        promocion.beneficios.length > 0 && (
                            <div className="mt-4 space-y-1.5 border-t border-border/60 pt-3">
                                <div className="flex items-center gap-1 text-[11px] font-black text-muted-foreground uppercase">
                                    <Gift className="size-3 text-primary dark:text-rose-400" />
                                    <span>Incluye en este paquete:</span>
                                </div>
                                {promocion.beneficios.slice(0, 3).map((ben) => (
                                    <div
                                        key={ben.id}
                                        className="flex items-center gap-2 truncate text-xs font-semibold text-foreground"
                                    >
                                        <CheckCircle2 className="size-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                        <span className="truncate">
                                            {ben.titulo}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                </div>

                {/* Precios y Botones */}
                <div className="mt-6 border-t border-border/60 pt-4">
                    <div className="mb-4 flex items-baseline justify-between">
                        <div className="flex flex-col">
                            {tieneDescuento &&
                                promocion.precio_original &&
                                promocion.precio_original >
                                    promocion.precio_final && (
                                    <span className="text-xs font-bold text-muted-foreground line-through">
                                        {promocion.moneda}
                                        {Number(
                                            promocion.precio_original,
                                        ).toFixed(0)}
                                    </span>
                                )}
                            <div className="flex items-baseline gap-1">
                                <span className="text-2xl font-black text-foreground">
                                    {promocion.moneda}
                                    {Number(promocion.precio_final).toFixed(0)}
                                </span>
                                <span className="text-xs font-bold text-muted-foreground">
                                    / paquete
                                </span>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => alSeleccionar(promocion)}
                            className="cursor-pointer rounded-full border-border text-xs font-bold hover:bg-muted"
                        >
                            <span>Detalles</span>
                            <ArrowRight className="ml-1 size-3" />
                        </Button>
                    </div>

                    <a
                        href={`https://wa.me/${telefonoLimpio}?text=${mensajeWhatsApp}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-2.5 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95 dark:bg-emerald-700 dark:hover:bg-emerald-800"
                    >
                        <MessageCircle className="size-4" />
                        <span>Aprovechar Oferta</span>
                    </a>
                </div>
            </div>
        </div>
    );
};

export default PromocionCard;
