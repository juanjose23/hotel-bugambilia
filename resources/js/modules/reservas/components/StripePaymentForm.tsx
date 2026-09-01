import { loadStripe } from '@stripe/stripe-js';
import type { Stripe, StripeElements } from '@stripe/stripe-js';
import { Loader2, ShieldCheck, Lock, AlertCircle, X } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import type { StripePaymentData } from '../types';

interface StripePaymentFormProps {
    stripeData: StripePaymentData;
    onSuccess: (paymentIntentId: string) => void;
    onError: (errorMessage: string) => void;
    onCancel?: () => void;
}

export const StripePaymentForm = ({
    stripeData,
    onSuccess,
    onError,
    onCancel,
}: StripePaymentFormProps) => {
    const [stripe, setStripe] = useState<Stripe | null>(null);
    const [elements, setElements] = useState<StripeElements | null>(null);
    const [loadingStripe, setLoadingStripe] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const paymentElementRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        let isMounted = true;

        const initStripe = async () => {
            try {
                const stripeInstance = await loadStripe(
                    stripeData.publishable_key,
                );

                if (!isMounted || !stripeInstance) {
                    return;
                }

                setStripe(stripeInstance);

                const isDark =
                    document.documentElement.classList.contains('dark');

                const elementsInstance = stripeInstance.elements({
                    clientSecret: stripeData.client_secret,
                    appearance: {
                        theme: isDark ? 'night' : 'stripe',
                        variables: {
                            colorPrimary: '#e11d48',
                            borderRadius: '12px',
                        },
                    },
                });

                const paymentElement = elementsInstance.create('payment', {
                    layout: {
                        type: 'tabs',
                        defaultCollapsed: false,
                    },
                    wallets: {
                        link: 'never',
                    },
                });

                if (paymentElementRef.current) {
                    paymentElement.mount(paymentElementRef.current);
                }

                if (isMounted) {
                    setElements(elementsInstance);
                    setLoadingStripe(false);
                }
            } catch (err: unknown) {
                if (isMounted) {
                    const msg =
                        err instanceof Error
                            ? err.message
                            : 'Error al inicializar la pasarela de pago.';
                    setErrorMessage(msg);
                    setLoadingStripe(false);
                    onError(msg);
                }
            }
        };

        initStripe();

        return () => {
            isMounted = false;
        };
    }, [stripeData.client_secret, stripeData.publishable_key, onError]);

    const handlePagar = async (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        setProcessing(true);
        setErrorMessage(null);

        try {
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.href,
                },
                redirect: 'if_required',
            });

            if (error) {
                const msg =
                    error.message || 'Ocurrió un error al procesar el pago.';
                setErrorMessage(msg);
                onError(msg);
                setProcessing(false);

                return;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
                onSuccess(paymentIntent.id);
            } else if (paymentIntent && paymentIntent.id) {
                onSuccess(paymentIntent.id);
            } else {
                setErrorMessage('El pago no pudo completarse con éxito.');
                setProcessing(false);
            }
        } catch (err: unknown) {
            const msg =
                err instanceof Error
                    ? err.message
                    : 'Error al procesar la transacción.';
            setErrorMessage(msg);
            onError(msg);
            setProcessing(false);
        }
    };

    const montoFormateado = `${stripeData.moneda ? stripeData.moneda + ' ' : '$ '}${Number(stripeData.monto).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    return (
        <form onSubmit={handlePagar} className="space-y-4 font-sans">
            <div className="flex items-center justify-between rounded-2xl border border-border bg-card p-4">
                <div>
                    <div className="text-xs font-bold text-muted-foreground">
                        Total a pagar ahora
                    </div>
                    <div className="text-2xl font-black text-foreground">
                        {montoFormateado}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <div className="flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        <ShieldCheck className="size-4" />
                        <span>Pago Seguro Stripe</span>
                    </div>
                    {onCancel && (
                        <button
                            type="button"
                            onClick={onCancel}
                            disabled={processing}
                            className="cursor-pointer rounded-full p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            title="Volver"
                        >
                            <X className="size-5" />
                        </button>
                    )}
                </div>
            </div>

            {errorMessage && (
                <div className="flex items-center gap-2 rounded-2xl border border-destructive/30 bg-destructive/10 p-3 text-xs font-bold text-destructive">
                    <AlertCircle className="size-4 shrink-0" />
                    <span>{errorMessage}</span>
                </div>
            )}

            {loadingStripe && (
                <div className="flex flex-col items-center justify-center gap-2 py-8 text-center text-muted-foreground">
                    <Loader2 className="size-6 animate-spin text-primary" />
                    <span className="text-xs font-bold">
                        Cargando pasarela de pago segura...
                    </span>
                </div>
            )}

            {/* Contenedor de Stripe Elements */}
            <div
                ref={paymentElementRef}
                className={`min-h-[160px] rounded-2xl border border-border bg-background p-4 ${loadingStripe ? 'hidden' : 'block'}`}
            />

            <div className="flex gap-3 pt-2">
                {onCancel && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={processing}
                        className="h-11 flex-1 rounded-2xl font-bold"
                    >
                        Volver
                    </Button>
                )}
                <Button
                    type="submit"
                    disabled={loadingStripe || processing || !stripe}
                    className="h-11 flex-2 rounded-2xl bg-primary font-black text-primary-foreground shadow-md hover:bg-primary/90"
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 size-4 animate-spin" />
                            <span>Procesando pago...</span>
                        </>
                    ) : (
                        <>
                            <Lock className="mr-2 size-4" />
                            <span>Pagar {montoFormateado}</span>
                        </>
                    )}
                </Button>
            </div>

            <div className="flex items-center justify-center gap-1.5 text-[11px] font-medium text-muted-foreground">
                <Lock className="size-3" />
                <span>
                    Encriptación TLS de 256 bits • Transacción protegida por
                    Stripe
                </span>
            </div>
        </form>
    );
};

export default StripePaymentForm;
