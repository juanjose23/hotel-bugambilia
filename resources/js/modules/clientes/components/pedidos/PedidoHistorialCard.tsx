import { Link } from '@inertiajs/react';
import { RotateCcw } from 'lucide-react';
import type { PortalPedidoResumen } from '@/modules/clientes/types';
import { Button } from '@/modules/shared/components/ui/button';

interface PedidoHistorialCardProps {
    pedido: PortalPedidoResumen;
}

export const PedidoHistorialCard = ({ pedido }: PedidoHistorialCardProps) => {
    return (
        <div className="flex flex-col justify-between gap-3 rounded-2xl border border-border/70 bg-card p-4 text-xs sm:flex-row sm:items-center">
            <div>
                <div className="flex items-center gap-2">
                    <span className="text-sm font-black text-foreground">
                        #{pedido.codigo}
                    </span>
                    <span className="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold text-muted-foreground">
                        {pedido.estado_label}
                    </span>
                </div>
                <p className="mt-0.5 text-[11px] text-muted-foreground">
                    {pedido.abierto_en} · {pedido.items.length} platillos
                </p>
            </div>

            <div className="flex items-center justify-between gap-4 sm:justify-end">
                <span className="text-sm font-black text-foreground">
                    {pedido.moneda} {pedido.total.toFixed(2)}
                </span>

                <Link href="/restaurante">
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 cursor-pointer gap-1 rounded-xl text-xs font-bold"
                    >
                        <RotateCcw className="size-3" />
                        <span>Pedir de nuevo</span>
                    </Button>
                </Link>
            </div>
        </div>
    );
};

export default PedidoHistorialCard;
