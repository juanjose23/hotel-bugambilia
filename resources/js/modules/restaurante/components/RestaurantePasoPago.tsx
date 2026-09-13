import {
    ArrowLeft,
    Banknote,
    CreditCard,
    DollarSign,
    Loader2,
    ShieldCheck,
} from 'lucide-react';
import type {
    FieldErrors,
    UseFormRegister,
    UseFormSetValue,
} from 'react-hook-form';
import type { StripePaymentData } from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import type { FormPedidoDelivery } from '../schemas/pedidoDeliverySchema';
import type { RespuestaPedidoBackend } from '../services/restauranteService';
import { RestaurantePagoStripePantalla } from './RestaurantePagoStripePantalla';
import { RestaurantePedidoCompletado } from './RestaurantePedidoCompletado';

interface RestaurantePasoPagoProps {
    register: UseFormRegister<FormPedidoDelivery>;
    setValue: UseFormSetValue<FormPedidoDelivery>;
    errors: FieldErrors<FormPedidoDelivery>;
    metodoPagoSeleccionado: 'efectivo' | 'stripe';
    isSubmitting: boolean;
    stripeData: StripePaymentData | null;
    confirmandoStripe: boolean;
    pedidoCreado: RespuestaPedidoBackend | null;
    total: number;
    moneda?: string;
    onStripeSuccess: (paymentIntentId: string) => Promise<void>;
    onCancelStripe: () => void;
    alRetroceder: () => void;
    alReiniciar: () => void;
}

export const RestaurantePasoPago = ({
    register,
    setValue,
    errors,
    metodoPagoSeleccionado,
    isSubmitting,
    stripeData,
    confirmandoStripe,
    pedidoCreado,
    total,
    moneda = 'C$',
    onStripeSuccess,
    onCancelStripe,
    alRetroceder,
    alReiniciar,
}: RestaurantePasoPagoProps) => {
    if (stripeData) {
        return (
            <RestaurantePagoStripePantalla
                stripeData={stripeData}
                pedidoCreado={pedidoCreado}
                total={total}
                moneda={moneda}
                confirmandoStripe={confirmandoStripe}
                onStripeSuccess={onStripeSuccess}
                onCancelStripe={onCancelStripe}
            />
        );
    }

    if (pedidoCreado) {
        return (
            <RestaurantePedidoCompletado
                pedidoCreado={pedidoCreado}
                alReiniciar={alReiniciar}
            />
        );
    }

    return (
        <div className="space-y-6">
            <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                <div className="border-b border-border/60 pb-4">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                            <CreditCard className="size-4" />
                        </div>
                        <h2 className="text-lg font-black text-foreground">
                            3. Selección de Método de Pago
                        </h2>
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Elige cómo deseas abonar tu orden al recibirla o de
                        forma online anticipada.
                    </p>
                </div>

                <div className="mt-6 space-y-5">
                    <FieldGroup>
                        <Field>
                            <FieldLabel className="text-xs font-bold">
                                Método de Pago Disponible *
                            </FieldLabel>
                            <div className="mt-1.5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setValue('metodoPago', 'efectivo', {
                                            shouldValidate: true,
                                            shouldDirty: true,
                                        })
                                    }
                                    className={`flex cursor-pointer items-start gap-3 rounded-2xl border p-4 text-left transition-all ${
                                        metodoPagoSeleccionado === 'efectivo'
                                            ? 'border-emerald-600 bg-emerald-500/10 text-emerald-950 ring-2 ring-emerald-500/30 dark:text-emerald-300'
                                            : 'border-border bg-card text-muted-foreground hover:bg-muted/50'
                                    }`}
                                >
                                    <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-600 dark:text-emerald-400">
                                        <Banknote className="size-5" />
                                    </div>
                                    <div>
                                        <div className="text-xs font-bold text-foreground">
                                            Efectivo contra Entrega
                                        </div>
                                        <div className="mt-0.5 text-[11px] text-muted-foreground">
                                            Paga en mano al repartidor cuando
                                            recibas tu pedido en Estelí.
                                        </div>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    onClick={() =>
                                        setValue('metodoPago', 'stripe', {
                                            shouldValidate: true,
                                            shouldDirty: true,
                                        })
                                    }
                                    className={`flex cursor-pointer items-start gap-3 rounded-2xl border p-4 text-left transition-all ${
                                        metodoPagoSeleccionado === 'stripe'
                                            ? 'border-primary bg-primary/10 text-foreground ring-2 ring-primary/30 dark:bg-rose-950/40'
                                            : 'border-border bg-card text-muted-foreground hover:bg-muted/50'
                                    }`}
                                >
                                    <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                                        <CreditCard className="size-5" />
                                    </div>
                                    <div>
                                        <div className="text-xs font-bold text-foreground">
                                            Tarjeta de Débito / Crédito
                                        </div>
                                        <div className="mt-0.5 text-[11px] text-muted-foreground">
                                            Pago seguro en línea con Visa,
                                            Mastercard o American Express.
                                        </div>
                                    </div>
                                </button>
                            </div>
                            {errors.metodoPago && (
                                <FieldError className="text-[11px]">
                                    {errors.metodoPago.message}
                                </FieldError>
                            )}
                        </Field>

                        {metodoPagoSeleccionado === 'efectivo' && (
                            <Field className="animate-in fade-in duration-200">
                                <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                    <DollarSign className="size-3.5 text-muted-foreground" />
                                    <span>
                                        ¿Con qué billete o monto pagas? (Para
                                        llevarte cambio exacto)
                                    </span>
                                </FieldLabel>
                                <Input
                                    placeholder="Ej: C$ 500 o $20 USD"
                                    className="h-10 max-w-sm rounded-xl text-xs"
                                    {...register('montoPagaCon')}
                                />
                            </Field>
                        )}
                    </FieldGroup>

                    <div className="flex items-center gap-2 rounded-2xl bg-muted/50 p-3 text-xs text-muted-foreground">
                        <ShieldCheck className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        <span>
                            Tus datos y comanda se procesan bajo protocolos de
                            seguridad y encriptación de extremo a extremo.
                        </span>
                    </div>
                </div>

                <div className="mt-8 flex items-center justify-between border-t border-border/60 pt-5">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alRetroceder}
                        className="h-11 cursor-pointer gap-2 rounded-2xl px-5 text-xs font-bold"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Atrás (Datos de Entrega)</span>
                    </Button>

                    <Button
                        type="submit"
                        disabled={isSubmitting}
                        className="h-11 cursor-pointer rounded-2xl bg-emerald-600 px-7 text-xs font-black text-white shadow-lg transition-all hover:bg-emerald-500 active:scale-95"
                    >
                        {isSubmitting ? (
                            <div className="flex items-center gap-2">
                                <Loader2 className="size-4 animate-spin" />
                                <span>Procesando comanda...</span>
                            </div>
                        ) : (
                            <span>
                                {metodoPagoSeleccionado === 'stripe'
                                    ? 'Proceder al Pago con Tarjeta'
                                    : 'Confirmar y Enviar Pedido'}
                            </span>
                        )}
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default RestaurantePasoPago;
