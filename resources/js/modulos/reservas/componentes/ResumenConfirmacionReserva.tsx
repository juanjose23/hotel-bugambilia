import { Building2, CreditCard, ShieldCheck, CheckCircle2 } from 'lucide-react';
import React from 'react';
import { Alert, AlertDescription } from '@/modulos/compartido/ui/alerta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { ConfiguracionStripePago } from '@/modulos/pagos/interfaces/pago';

interface PropiedadesResumenConfirmacion {
    nombreRecurso: string;
    categoriaRecurso: string;
    nombreCliente: string;
    telefonoCliente: string;
    fechaCheckIn: string;
    fechaCheckOut: string;
    nochesCalculadas: number;
    adultos: number;
    ninos: number;
    moneda: string;
    subtotalEstimado: number;
    tipoPagoReserva?: 'sin_pago' | 'abono_50' | 'pago_completo';
    canalPagoReserva?: 'manual' | 'stripe' | 'transferencia' | 'sin_pago';
    stripePago?: ConfiguracionStripePago | null;
    preparandoStripe?: boolean;
    errorStripe?: string | null;
    onCanalPagoChange?: (canal: 'stripe' | 'transferencia') => void;
}

export function ResumenConfirmacionReserva({
    nombreRecurso,
    categoriaRecurso,
    nombreCliente,
    telefonoCliente,
    fechaCheckIn,
    fechaCheckOut,
    nochesCalculadas,
    adultos,
    ninos,
    moneda,
    subtotalEstimado,
    tipoPagoReserva = 'abono_50',
    canalPagoReserva = 'stripe',
    stripePago = null,
    preparandoStripe = false,
    errorStripe = null,
    onCanalPagoChange,
}: PropiedadesResumenConfirmacion) {
    const montoGarantia =
        tipoPagoReserva === 'pago_completo'
            ? subtotalEstimado
            : tipoPagoReserva === 'abono_50'
              ? subtotalEstimado * 0.5
              : 0;

    return (
        <div className="animate-in fade-in-50 flex flex-col gap-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
            <div className="flex flex-col gap-0.5">
                <h2 className="text-lg font-black text-foreground md:text-xl">
                    Resumen de la{' '}
                    <span className="font-serif font-normal text-primary italic">
                        Reserva
                    </span>
                </h2>
                <p className="text-xs text-muted-foreground">
                    Por favor revise los detalles antes de confirmar su
                    solicitud
                </p>
            </div>

            <div className="flex flex-col gap-3 rounded-2xl border border-border bg-background p-4 text-xs">
                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">
                        Tipo reservado:
                    </span>
                    <span className="font-black text-foreground">
                        {nombreRecurso} ({categoriaRecurso})
                    </span>
                </div>

                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Titular:</span>
                    <span className="font-bold text-foreground">
                        {nombreCliente} ({telefonoCliente})
                    </span>
                </div>

                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Fechas:</span>
                    <span className="font-bold text-foreground">
                        {fechaCheckIn} a {fechaCheckOut} ({nochesCalculadas}{' '}
                        noche
                        {nochesCalculadas > 1 ? 's' : ''})
                    </span>
                </div>

                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Huéspedes:</span>
                    <span className="font-bold text-foreground">
                        {adultos} adulto(s), {ninos} niño(s)
                    </span>
                </div>

                <div className="flex items-center justify-between pt-2">
                    <span className="text-sm font-bold text-foreground">
                        Total Estimado:
                    </span>
                    <span className="text-xl font-black text-primary">
                        {moneda} {formatearNumero(subtotalEstimado)}
                    </span>
                </div>

                {montoGarantia > 0 && (
                    <div className="flex items-center justify-between border-t border-border/40 pt-3">
                        <span className="text-muted-foreground">
                            Garantía requerida (50%):
                        </span>
                        <span className="font-black text-emerald-600 dark:text-emerald-400">
                            {moneda} {formatearNumero(montoGarantia)}
                        </span>
                    </div>
                )}
            </div>

            {montoGarantia > 0 && (
                <div className="flex flex-col gap-4">
                    <div className="flex flex-col gap-0.5">
                        <h3 className="text-sm font-black text-foreground">
                            Método de Pago
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            Seleccione cómo desea completar el pago de garantía
                            de su estancia.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            onClick={() => onCanalPagoChange?.('stripe')}
                            className={`flex items-center gap-3 rounded-2xl border p-4 text-left transition ${
                                canalPagoReserva === 'stripe'
                                    ? 'border-primary bg-primary/10 text-primary ring-2 ring-primary/20'
                                    : 'border-border bg-background text-foreground hover:border-primary/50'
                            }`}
                        >
                            <CreditCard className="size-5 shrink-0" />
                            <span className="flex flex-col gap-0.5">
                                <span className="block text-xs font-black">
                                    Tarjeta con Stripe
                                </span>
                                <span className="block text-[11px] text-muted-foreground">
                                    Confirmación automática en línea.
                                </span>
                            </span>
                        </button>

                        <button
                            type="button"
                            onClick={() => onCanalPagoChange?.('transferencia')}
                            className={`flex items-center gap-3 rounded-2xl border p-4 text-left transition ${
                                canalPagoReserva === 'transferencia'
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-700 ring-2 ring-emerald-500/20 dark:text-emerald-300'
                                    : 'border-border bg-background text-foreground hover:border-emerald-500/50'
                            }`}
                        >
                            <Building2 className="size-5 shrink-0" />
                            <span className="flex flex-col gap-0.5">
                                <span className="block text-xs font-black">
                                    Transferencia bancaria
                                </span>
                                <span className="block text-[11px] text-muted-foreground">
                                    Se registra como transferencia.
                                </span>
                            </span>
                        </button>
                    </div>

                    {canalPagoReserva === 'stripe' && (
                        <div className="flex flex-col gap-3 rounded-3xl border border-border bg-background p-5">
                            <div className="flex items-center gap-2">
                                <CreditCard className="size-4 text-primary" />
                                <span className="text-xs font-black text-foreground">
                                    Pago seguro con Stripe
                                </span>
                            </div>

                            {preparandoStripe && (
                                <p className="text-xs font-semibold text-muted-foreground">
                                    Preparando formulario seguro de tarjeta...
                                </p>
                            )}

                            {errorStripe && (
                                <Alert variant="destructive">
                                    <AlertDescription className="text-xs font-semibold">
                                        {errorStripe}
                                    </AlertDescription>
                                </Alert>
                            )}

                            {!stripePago &&
                                !preparandoStripe &&
                                !errorStripe && (
                                    <p className="text-xs text-muted-foreground">
                                        Al confirmar la reserva se cargará el
                                        formulario de tarjeta en esta misma
                                        pantalla.
                                    </p>
                                )}

                            <div
                                id="stripe-reserva-payment-element"
                                className={stripePago ? 'min-h-24' : 'min-h-0'}
                            />
                        </div>
                    )}

                    {canalPagoReserva === 'transferencia' && (
                        <div className="flex flex-col gap-2 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-5 text-xs text-foreground">
                            <div className="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-300">
                                <CheckCircle2 className="size-4" />
                                <span>
                                    Instrucciones para Transferencia Bancaria
                                </span>
                            </div>
                            <p className="text-muted-foreground">
                                Realice su transferencia de garantía ($
                                {formatearNumero(montoGarantia)}) a la siguiente
                                cuenta bancaria. Su reserva quedará registrada
                                en estado pendiente de verificación.
                            </p>
                            <div className="mt-1 flex flex-col gap-1 rounded-2xl border border-border/40 bg-background p-3 font-mono text-[11px]">
                                <div>
                                    <strong>Banco:</strong> BAC Credomatic
                                </div>
                                <div>
                                    <strong>Cuenta de Ahorros:</strong>{' '}
                                    360-984-123456
                                </div>
                                <div>
                                    <strong>Titular:</strong> Hotel Bugambilias
                                    S.A.
                                </div>
                                <div>
                                    <strong>Concepto:</strong> Reserva{' '}
                                    {nombreCliente}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}

            <Alert className="border-emerald-500/20 bg-emerald-500/10 text-emerald-800 dark:text-emerald-300">
                <ShieldCheck className="size-5 text-emerald-600 dark:text-emerald-400" />
                <AlertDescription className="pl-2 text-xs font-medium">
                    Su reserva será procesada con confirmación inmediata y
                    garantía de satisfacción Hotel Bugambilias.
                </AlertDescription>
            </Alert>
        </div>
    );
}
