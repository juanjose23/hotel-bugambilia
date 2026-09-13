import { Link } from '@inertiajs/react';
import { CheckCircle2, ChefHat, MessageSquare } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { RespuestaPedidoBackend } from '../services/restauranteService';

interface RestaurantePedidoCompletadoProps {
    pedidoCreado: RespuestaPedidoBackend;
    alReiniciar: () => void;
}

export const RestaurantePedidoCompletado = ({
    pedidoCreado,
    alReiniciar,
}: RestaurantePedidoCompletadoProps) => {
    return (
        <div className="animate-in fade-in zoom-in-95 rounded-3xl border border-border/80 bg-card p-8 text-center shadow-xs duration-300">
            <div className="mx-auto flex size-16 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <CheckCircle2 className="size-9" />
            </div>

            <span className="mt-4 inline-block rounded-full bg-emerald-500/10 px-3.5 py-1 text-xs font-black tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                Comanda Registrada en Cocina
            </span>

            <h3 className="mt-3 text-2xl font-black text-foreground">
                ¡Pedido #{pedidoCreado.codigo}!
            </h3>

            <p className="mx-auto mt-2 max-w-md text-xs text-muted-foreground">
                Hemos recibido tu pedido a domicilio exitosamente. La cocina del
                Hotel Bugambilias ya se encuentra preparando tus platillos.
            </p>

            <div className="mt-4 inline-flex items-center gap-2 rounded-2xl bg-muted/60 px-4 py-2 text-xs font-bold">
                <span>Total de la Orden:</span>
                <strong className="text-sm font-black text-primary">
                    {pedidoCreado.moneda} {pedidoCreado.total.toFixed(2)}
                </strong>
            </div>

            <div className="mx-auto mt-8 flex max-w-lg flex-col flex-wrap items-center justify-center gap-3 sm:flex-row">
                <Link
                    href="/portal/pedidos"
                    className="flex w-full flex-1 items-center justify-center gap-2 rounded-2xl bg-primary px-5 py-3 text-xs font-black text-white shadow-md transition-all hover:bg-primary/90 active:scale-95 sm:w-auto"
                >
                    <ChefHat className="size-4" />
                    <span>Ver en Mi Portal</span>
                </Link>

                {pedidoCreado.whatsapp_url && (
                    <a
                        href={pedidoCreado.whatsapp_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex w-full flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-500 active:scale-95 sm:w-auto"
                    >
                        <MessageSquare className="size-4" />
                        <span>Comanda en WhatsApp</span>
                    </a>
                )}

                <Button
                    type="button"
                    variant="outline"
                    onClick={alReiniciar}
                    className="h-11 w-full cursor-pointer rounded-2xl px-5 text-xs font-bold sm:w-auto"
                >
                    Pedir otro platillo
                </Button>
            </div>
        </div>
    );
};

export default RestaurantePedidoCompletado;
