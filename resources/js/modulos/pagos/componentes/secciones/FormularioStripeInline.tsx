import {
    CreditCard,
    LoaderCircle,
    AlertCircle,
    ShieldCheck,
} from 'lucide-react';
import React, { useEffect, useRef, useState } from 'react';
import { Alert, AlertDescription } from '@/modulos/compartido/ui/alerta';
import type { StripeElementsInstance } from '@/modulos/pagos/interfaces/pago';

interface PropiedadesFormularioStripeInline {
    cargando?: boolean;
    error?: string | null;
    publishableKey?: string | null;
    clientSecret?: string | null;
    onReady?: () => void;
}

export function FormularioStripeInline({
    cargando = false,
    error = null,
    publishableKey,
    clientSecret,
    onReady,
}: PropiedadesFormularioStripeInline) {
    const [stripeMontado, setStripeMontado] = useState(false);
    const [mensajeError, setMensajeError] = useState<string | null>(null);
    const elementsRef = useRef<StripeElementsInstance | null>(null);

    useEffect(() => {
        if (!publishableKey || !clientSecret || elementsRef.current) {
            return;
        }

        const montarStripe = () => {
            if (!window.Stripe) {
                setMensajeError('Stripe.js no está disponible.');

                return;
            }

            try {
                const stripe = window.Stripe(publishableKey);
                const elements = stripe.elements({ clientSecret });
                elements
                    .create('payment', { layout: 'tabs' })
                    .mount('#stripe-inline-element');
                elementsRef.current = elements;
                setStripeMontado(true);
                onReady?.();
            } catch (err: unknown) {
                const msg =
                    err instanceof Error
                        ? err.message
                        : 'Error al montar la pasarela Stripe.';
                setMensajeError(msg);
            }
        };

        if (window.Stripe) {
            montarStripe();

            return;
        }

        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = montarStripe;
        script.onerror = () => setMensajeError('No se pudo cargar Stripe.js.');
        document.head.appendChild(script);
    }, [publishableKey, clientSecret, onReady]);

    return (
        <div className="flex flex-col gap-4 rounded-3xl border border-border/80 bg-background p-5 shadow-xs transition-all duration-300">
            <div className="flex items-center justify-between border-b border-border/40 pb-3">
                <div className="flex items-center gap-2">
                    <span className="flex size-8 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <CreditCard className="size-4" />
                    </span>
                    <span className="text-xs font-extrabold text-foreground">
                        Pago Seguro con Tarjeta (Stripe 256-bit SSL)
                    </span>
                </div>
                <div className="flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    <ShieldCheck className="size-3.5" />
                    <span>Encriptado</span>
                </div>
            </div>

            {cargando && (
                <div className="flex items-center justify-center gap-2 py-6 text-xs font-semibold text-muted-foreground">
                    <LoaderCircle className="size-4 animate-spin text-primary" />
                    <span>Inicializando pasarela de pago segura...</span>
                </div>
            )}

            {!publishableKey || !clientSecret ? (
                <div className="flex flex-col gap-2 rounded-2xl bg-muted/40 p-4 text-xs text-muted-foreground">
                    <p className="font-semibold text-foreground">
                        Confirmación con garantía de tarjeta
                    </p>
                    <p>
                        Al hacer clic en "Confirmar reserva garantizada", sus
                        datos de tarjeta serán procesados de forma segura
                        directamente por Stripe.
                    </p>
                </div>
            ) : null}

            <div
                id="stripe-inline-element"
                className={stripeMontado ? 'min-h-24' : 'min-h-12'}
            />

            {(error || mensajeError) && (
                <Alert variant="destructive">
                    <AlertCircle className="size-4" />
                    <AlertDescription className="text-xs font-semibold">
                        {error || mensajeError}
                    </AlertDescription>
                </Alert>
            )}
        </div>
    );
}
