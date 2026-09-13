import { Clock } from 'lucide-react';
import type { PortalPedidoResumen } from '@/modules/clientes/types';

interface PedidoActivoCardProps {
    pedido: PortalPedidoResumen;
}

export const PedidoActivoCard = ({ pedido }: PedidoActivoCardProps) => {
    return (
        <div className="space-y-4 rounded-3xl border border-primary/20 bg-card p-6 shadow-xs">
            <div className="flex flex-col justify-between gap-3 border-b border-border/60 pb-3 sm:flex-row sm:items-center">
                <div>
                    <div className="flex items-center gap-2">
                        <span className="text-lg font-black text-foreground">
                            #{pedido.codigo}
                        </span>
                        <span className="rounded-full bg-emerald-500/10 px-3 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            {pedido.estado_label}
                        </span>
                    </div>
                    <p className="mt-0.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Clock className="size-3.5" />
                        <span>Registrado: {pedido.abierto_en}</span>
                    </p>
                </div>

                <div className="text-right">
                    <span className="block text-xs text-muted-foreground">
                        Total a Pagar
                    </span>
                    <span className="text-xl font-black text-primary dark:text-rose-400">
                        {pedido.moneda} {pedido.total.toFixed(2)}
                    </span>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                {pedido.items.map((item) => (
                    <div
                        key={item.id}
                        className="rounded-2xl border border-border/60 bg-muted/40 p-3 text-xs"
                    >
                        <p className="truncate font-bold text-foreground">
                            {item.nombre}
                        </p>
                        <p className="mt-0.5 flex justify-between text-[11px] text-muted-foreground">
                            <span>Cantidad: {item.cantidad}</span>
                            <strong className="text-foreground">
                                {pedido.moneda} {item.subtotal.toFixed(2)}
                            </strong>
                        </p>
                        {item.observaciones && (
                            <p className="mt-1 rounded bg-muted/80 px-1.5 py-0.5 text-[10px] text-muted-foreground italic">
                                💬 {item.observaciones}
                            </p>
                        )}
                    </div>
                ))}
            </div>

            {pedido.notas && (
                <div className="rounded-2xl bg-muted/40 p-3 text-xs text-muted-foreground">
                    <span className="font-bold text-foreground">
                        Detalles de Entrega:{' '}
                    </span>
                    <span>{pedido.notas}</span>
                </div>
            )}
        </div>
    );
};

export default PedidoActivoCard;
