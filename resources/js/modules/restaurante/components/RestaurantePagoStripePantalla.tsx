import { CreditCard, Loader2 } from 'lucide-react';
import { StripePaymentForm } from '@/modules/reservas/components/StripePaymentForm';
import type { StripePaymentData } from '@/modules/reservas/types';
import type { RespuestaPedidoBackend } from '../services/restauranteService';

interface RestaurantePagoStripePantallaProps {
    stripeData: StripePaymentData;
    pedidoCreado: RespuestaPedidoBackend | null;
    total: number;
    moneda: string;
    confirmandoStripe: boolean;
    onStripeSuccess: (paymentIntentId: string) => Promise<void>;
    onCancelStripe: () => void;
}

export const RestaurantePagoStripePantalla = ({
    stripeData,
    pedidoCreado,
    total,
    moneda,
    confirmandoStripe,
    onStripeSuccess,
    onCancelStripe,
}: RestaurantePagoStripePantallaProps) => {
    return (
        <div className="animate-in fade-in zoom-in-95 space-y-5 rounded-3xl border border-border/80 bg-card p-6 shadow-xs duration-300">
            <div className="border-b border-border/60 pb-4">
                <div className="flex items-center gap-2">
                    <div className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                        <CreditCard className="size-4" />
                    </div>
                    <h2 className="text-lg font-black text-foreground">
                        Pasarela de Pago Segura con Tarjeta (Stripe)
                    </h2>
                </div>
                <p className="mt-1 text-xs text-muted-foreground">
                    Comanda registrada:{' '}
                    <strong className="text-foreground">
                        #{pedidoCreado?.codigo}
                    </strong>
                    . Total a pagar:{' '}
                    <strong className="text-primary">
                        {moneda} {total.toFixed(2)}
                    </strong>
                    .
                </p>
            </div>

            <div className="rounded-2xl border border-primary/20 bg-primary/5 p-4 text-xs text-muted-foreground">
                <p className="font-bold text-foreground">
                    Procesamiento Cifrado SSL
                </p>
                <p className="mt-0.5 text-[11px]">
                    Ingresa los 16 dígitos de tu tarjeta, fecha de vencimiento y
                    código CVC. No almacenamos los datos sensibles de tu
                    tarjeta.
                </p>
            </div>

            <StripePaymentForm
                stripeData={stripeData}
                onSuccess={onStripeSuccess}
                onError={(msg) => console.error('Stripe Error:', msg)}
                onCancel={onCancelStripe}
            />

            {confirmandoStripe && (
                <div className="flex items-center justify-center gap-2 rounded-2xl bg-muted/60 p-4 text-xs font-bold text-muted-foreground">
                    <Loader2 className="size-4 animate-spin text-primary" />
                    <span>
                        Verificando y confirmando transacción bancaria...
                    </span>
                </div>
            )}
        </div>
    );
};

export default RestaurantePagoStripePantalla;
