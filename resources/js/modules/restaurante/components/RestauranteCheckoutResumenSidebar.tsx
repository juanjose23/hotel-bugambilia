import {
    ShoppingBag,
    Truck,
    Clock,
    Lock,
    Utensils,
    CalendarCheck,
    AlertCircle,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { ItemCarritoRestaurante } from '../types';

interface RestauranteCheckoutResumenSidebarProps {
    items: ItemCarritoRestaurante[];
    subtotal: number;
    costoDelivery: number;
    total: number;
    moneda?: string;
    departamento?: string;
    municipio?: string;
    pedidoMinimo?: number;
    alAbrirReservaMesa?: () => void;
}

export const RestauranteCheckoutResumenSidebar = ({
    items,
    subtotal,
    costoDelivery,
    total,
    moneda = 'C$',
    departamento = 'Estelí',
    pedidoMinimo = 0,
    alAbrirReservaMesa,
}: RestauranteCheckoutResumenSidebarProps) => {
    const cumpleMinimo = subtotal >= pedidoMinimo;

    return (
        <div className="space-y-6 lg:sticky lg:top-20">
            {/* Tarjeta de Resumen Financiero */}
            <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                <div className="flex items-center gap-2 border-b border-border/60 pb-4">
                    <div className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                        <ShoppingBag className="size-4" />
                    </div>
                    <h3 className="text-base font-black text-foreground">
                        Resumen de la Orden
                    </h3>
                </div>

                {/* Lista compacta de items */}
                <div className="mt-4 max-h-56 space-y-2.5 divide-y divide-border/60 overflow-y-auto pr-1">
                    {items.length === 0 ? (
                        <p className="py-4 text-center text-xs text-muted-foreground italic">
                            No hay platillos seleccionados
                        </p>
                    ) : (
                        items.map(({ plato, cantidad }) => (
                            <div
                                key={plato.id}
                                className="flex items-center justify-between pt-2.5 text-xs first:pt-0"
                            >
                                <div className="min-w-0 flex-1 pr-2">
                                    <p className="truncate font-bold text-foreground">
                                        {plato.nombre}
                                    </p>
                                    <p className="text-[11px] text-muted-foreground">
                                        {cantidad}x {moneda}{' '}
                                        {Number(plato.precio || 0).toFixed(2)}
                                    </p>
                                </div>
                                <span className="shrink-0 font-black text-foreground">
                                    {moneda}{' '}
                                    {(
                                        Number(plato.precio || 0) * cantidad
                                    ).toFixed(2)}
                                </span>
                            </div>
                        ))
                    )}
                </div>

                {/* Desglose de Precios */}
                <div className="mt-4 space-y-2.5 border-t border-border/60 pt-4 text-xs">
                    <div className="flex justify-between text-muted-foreground">
                        <span>
                            Subtotal (
                            {items.reduce((acc, i) => acc + i.cantidad, 0)}{' '}
                            items):
                        </span>
                        <span className="font-medium text-foreground">
                            {moneda} {subtotal.toFixed(2)}
                        </span>
                    </div>

                    <div className="flex justify-between text-muted-foreground">
                        <span className="inline-flex items-center gap-1.5">
                            <Truck className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>Envío ({departamento}):</span>
                        </span>
                        <span className="font-medium text-foreground">
                            {moneda} {costoDelivery.toFixed(2)}
                        </span>
                    </div>

                    <div className="flex justify-between text-[11px] text-muted-foreground">
                        <span className="inline-flex items-center gap-1.5">
                            <Clock className="size-3.5 text-muted-foreground" />
                            <span>Tiempo estimado:</span>
                        </span>
                        <span className="font-bold text-foreground">
                            30 - 45 min
                        </span>
                    </div>

                    <div className="flex items-baseline justify-between border-t border-border/60 pt-3 text-sm font-black text-foreground">
                        <span>Total a Pagar:</span>
                        <span className="text-xl font-black text-primary dark:text-rose-400">
                            {moneda} {total.toFixed(2)}
                        </span>
                    </div>
                </div>

                {/* Alerta de Pedido Mínimo si no cumple */}
                {!cumpleMinimo && pedidoMinimo > 0 && (
                    <div className="mt-4 flex items-start gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-700 dark:text-amber-300">
                        <AlertCircle className="mt-0.5 size-4 shrink-0" />
                        <span>
                            El pedido mínimo para delivery es de {moneda}{' '}
                            {pedidoMinimo.toFixed(2)}. Faltan {moneda}{' '}
                            {(pedidoMinimo - subtotal).toFixed(2)}.
                        </span>
                    </div>
                )}

                {/* Badge de seguridad */}
                <div className="mt-5 flex items-center justify-center gap-2 rounded-2xl border border-border/40 bg-muted/40 p-2.5 text-[11px] font-medium text-muted-foreground">
                    <Lock className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span>Garantía de Frescura & Entrega Segura</span>
                </div>
            </div>

            {/* Banner / Opción de Reserva de Mesa */}
            {alAbrirReservaMesa && (
                <div className="space-y-3 rounded-3xl border border-primary/20 bg-gradient-to-br from-primary/5 via-card to-primary/10 p-5 text-xs shadow-xs">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 items-center justify-center rounded-xl bg-primary/20 text-primary dark:text-rose-300">
                            <Utensils className="size-4" />
                        </div>
                        <h4 className="text-sm font-black text-foreground">
                            ¿Prefieres cenar en el restaurante?
                        </h4>
                    </div>
                    <p className="text-[11px] leading-relaxed text-muted-foreground">
                        Reserva una mesa en nuestros salones climatizados o
                        terraza al aire libre con atención exclusiva.
                    </p>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alAbrirReservaMesa}
                        className="h-9 w-full cursor-pointer gap-2 rounded-2xl border-primary/30 text-xs font-bold hover:bg-primary/10"
                    >
                        <CalendarCheck className="size-3.5 text-primary" />
                        <span>Reservar una Mesa</span>
                    </Button>
                </div>
            )}
        </div>
    );
};

export default RestauranteCheckoutResumenSidebar;
