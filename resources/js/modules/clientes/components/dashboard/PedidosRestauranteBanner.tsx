import { Link } from '@inertiajs/react';
import {
    UtensilsCrossed,
    Clock,
    Truck,
    ChefHat,
    ShoppingBag,
    Plus,
    ArrowRight,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { PortalPedidoResumen } from '../../types';

interface PedidosRestauranteBannerProps {
    pedidosActivos: PortalPedidoResumen[];
    historialPedidos: PortalPedidoResumen[];
}

export const PedidosRestauranteBanner = ({
    pedidosActivos,
    historialPedidos,
}: PedidosRestauranteBannerProps) => {
    const pedidoActivo = pedidosActivos[0];
    const totalPedidos = pedidosActivos.length + historialPedidos.length;

    if (totalPedidos === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-8 text-center sm:p-12">
                <UtensilsCrossed className="mx-auto size-12 text-muted-foreground/60" />
                <h3 className="mt-3 text-lg font-bold text-foreground">
                    No tienes ningún pedido de restaurante en este momento
                </h3>
                <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                    Disfruta de nuestra gastronomía de alta cocina, platillos
                    típicos y servicio a domicilio en Estelí.
                </p>
                <div className="mt-5">
                    <Link
                        href="/restaurante"
                        className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 hover:bg-primary/90"
                    >
                        <ShoppingBag className="size-4" />
                        <span>Ver Menú y Pedir a Domicilio</span>
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Pedido Activo en Preparación / Entrega */}
            {pedidoActivo ? (
                <div className="relative overflow-hidden rounded-3xl border border-primary/20 bg-gradient-to-br from-card via-card to-primary/5 p-6 shadow-sm">
                    <div className="flex flex-col justify-between gap-4 border-b border-border/60 pb-4 md:flex-row md:items-center">
                        <div className="flex items-center gap-3">
                            <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:text-rose-400">
                                <ChefHat className="size-6 animate-pulse" />
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="text-xs font-black tracking-wider text-primary uppercase">
                                        Comanda en Cocina / Delivery
                                    </span>
                                    <span className="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        {pedidoActivo.estado_label}
                                    </span>
                                </div>
                                <h3 className="mt-0.5 text-xl font-black text-foreground">
                                    Orden #{pedidoActivo.codigo}
                                </h3>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <div className="text-right">
                                <span className="block text-xs text-muted-foreground">
                                    Total de la Orden
                                </span>
                                <span className="text-lg font-black text-primary dark:text-rose-400">
                                    {pedidoActivo.moneda}{' '}
                                    {pedidoActivo.total.toFixed(2)}
                                </span>
                            </div>

                            <Link href="/restaurante">
                                <Button
                                    size="sm"
                                    className="h-9 cursor-pointer gap-1.5 rounded-2xl text-xs font-bold"
                                >
                                    <Plus className="size-3.5" />
                                    <span>Pedir Más</span>
                                </Button>
                            </Link>
                        </div>
                    </div>

                    {/* Desglose de platillos */}
                    <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {pedidoActivo.items.map((item) => (
                            <div
                                key={item.id}
                                className="flex items-center justify-between rounded-2xl border border-border/60 bg-muted/40 p-3 text-xs"
                            >
                                <div className="min-w-0 pr-2">
                                    <p className="truncate font-bold text-foreground">
                                        {item.nombre}
                                    </p>
                                    <p className="text-[11px] text-muted-foreground">
                                        {item.cantidad}x ({pedidoActivo.moneda}{' '}
                                        {item.precio_unitario.toFixed(2)})
                                    </p>
                                </div>
                                <span className="shrink-0 font-black text-foreground">
                                    {pedidoActivo.moneda}{' '}
                                    {item.subtotal.toFixed(2)}
                                </span>
                            </div>
                        ))}
                    </div>

                    {/* Footer con fecha e indicador */}
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-border/40 pt-2 text-xs text-muted-foreground">
                        <div className="flex items-center gap-3">
                            <span className="inline-flex items-center gap-1">
                                <Clock className="size-3.5" />
                                <span>{pedidoActivo.abierto_en}</span>
                            </span>
                            {pedidoActivo.es_delivery && (
                                <span className="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                    <Truck className="size-3.5" />
                                    <span>Entrega a Domicilio</span>
                                </span>
                            )}
                        </div>

                        <Link
                            href="/portal/pedidos"
                            className="inline-flex cursor-pointer items-center gap-1 text-xs font-bold text-primary hover:underline"
                        >
                            <span>Ver todas mis comandas</span>
                            <ArrowRight className="size-3.5" />
                        </Link>
                    </div>
                </div>
            ) : null}
        </div>
    );
};

export default PedidosRestauranteBanner;
