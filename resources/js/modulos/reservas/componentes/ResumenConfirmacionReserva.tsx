import {
    Building2,
    CreditCard,
    ShieldCheck,
    CheckCircle2,
    Calendar,
    Users,
    Receipt,
    User,
    Sparkles,
    Info,
    Lock,
} from 'lucide-react';
import React, { useState } from 'react';
import { Alert, AlertDescription } from '@/modulos/compartido/ui/alerta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { ConfiguracionStripePago } from '@/modulos/pagos/interfaces/pago';
import { ModalCondicionesPagoCancelacion } from './ModalCondicionesPagoCancelacion';

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
    const [modalCondicionesAbierto, setModalCondicionesAbierto] = useState(false);

    const montoGarantia =
        tipoPagoReserva === 'pago_completo'
            ? subtotalEstimado
            : tipoPagoReserva === 'abono_50'
              ? subtotalEstimado * 0.5
              : 0;

    return (
        <div className="animate-in fade-in-50 slide-in-from-bottom-2 flex flex-col gap-6 rounded-3xl border border-border/80 bg-card p-5 shadow-2xs duration-300 md:p-8">
            <div className="flex items-center justify-between border-b border-border/60 pb-4">
                <div>
                    <h2 className="text-lg font-black text-foreground md:text-xl">
                        Resumen de la <span className="text-bugambilia-600 dark:text-bugambilia-400">Reserva</span>
                    </h2>
                    <p className="text-xs font-medium text-muted-foreground">
                        Por favor verifique los detalles antes de procesar su solicitud
                    </p>
                </div>

                <div className="flex size-10 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                    <Receipt className="size-5" />
                </div>
            </div>

            {/* Tarjetas de Datos de la Reserva */}
            <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 text-xs">
                <div className="flex items-start gap-3 rounded-2xl border border-border/60 bg-background p-3.5">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <Building2 className="size-4" />
                    </div>
                    <div>
                        <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                            Hospedaje Seleccionado
                        </span>
                        <span className="font-black text-foreground text-xs">
                            {nombreRecurso}
                        </span>
                        <span className="block text-[10px] text-muted-foreground font-semibold">
                            Categoría: {categoriaRecurso}
                        </span>
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-2xl border border-border/60 bg-background p-3.5">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <User className="size-4" />
                    </div>
                    <div>
                        <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                            Titular de la Reserva
                        </span>
                        <span className="font-black text-foreground text-xs">
                            {nombreCliente}
                        </span>
                        <span className="block text-[10px] text-muted-foreground font-semibold">
                            Tel: {telefonoCliente}
                        </span>
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-2xl border border-border/60 bg-background p-3.5">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <Calendar className="size-4" />
                    </div>
                    <div>
                        <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                            Fechas de Estancia
                        </span>
                        <span className="font-black text-foreground text-xs">
                            {fechaCheckIn} a {fechaCheckOut}
                        </span>
                        <span className="block text-[10px] text-muted-foreground font-semibold">
                            Duración: {nochesCalculadas} noche{nochesCalculadas > 1 ? 's' : ''}
                        </span>
                    </div>
                </div>

                <div className="flex items-start gap-3 rounded-2xl border border-border/60 bg-background p-3.5">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <Users className="size-4" />
                    </div>
                    <div>
                        <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                            Capacidad Registrada
                        </span>
                        <span className="font-black text-foreground text-xs">
                            {adultos} Adulto(s)
                        </span>
                        <span className="block text-[10px] text-muted-foreground font-semibold">
                            {ninos > 0 ? `${ninos} Niño(s)` : 'Sin menores'}
                        </span>
                    </div>
                </div>
            </div>

            {/* Total Estimado & Garantía Banner */}
            <div className="rounded-3xl border border-bugambilia-500/30 bg-gradient-to-r from-bugambilia-500/10 via-card to-background p-5">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span className="block text-[10px] font-extrabold text-muted-foreground uppercase tracking-wider">
                            Total Estimado de la Estancia
                        </span>
                        <span className="font-mono text-2xl font-black text-foreground">
                            {moneda} ${formatearNumero(subtotalEstimado)}
                        </span>
                    </div>

                    {montoGarantia > 0 && (
                        <div className="rounded-2xl bg-card border border-border/80 px-4 py-2 text-right">
                            <span className="block text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                Garantía de Reserva (50%)
                            </span>
                            <span className="font-mono text-lg font-black text-emerald-600 dark:text-emerald-400">
                                {moneda} ${formatearNumero(montoGarantia)}
                            </span>
                        </div>
                    )}
                </div>
            </div>

            {/* Selector de Método de Pago */}
            {montoGarantia > 0 && (
                <div className="space-y-4">
                    <div>
                        <h3 className="text-xs font-black text-foreground uppercase tracking-wider">
                            Seleccione su Método de Pago de Garantía
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            Complete el pago en línea de forma segura o por transferencia bancaria.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            onClick={() => onCanalPagoChange?.('stripe')}
                            className={`flex items-center gap-3 rounded-2xl border p-4 text-left transition-all cursor-pointer ${
                                canalPagoReserva === 'stripe'
                                    ? 'border-bugambilia-600 bg-bugambilia-500/10 text-bugambilia-700 ring-2 ring-bugambilia-500/20 dark:text-bugambilia-300'
                                    : 'border-border bg-background text-foreground hover:border-bugambilia-500/50'
                            }`}
                        >
                            <CreditCard className="size-5 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                            <div className="grow min-w-0">
                                <span className="block text-xs font-black">
                                    Tarjeta con Stripe
                                </span>
                                <span className="block text-[11px] text-muted-foreground truncate">
                                    Confirmación automática e inmediata
                                </span>
                            </div>
                            {canalPagoReserva === 'stripe' && (
                                <CheckCircle2 className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                            )}
                        </button>

                        <button
                            type="button"
                            onClick={() => onCanalPagoChange?.('transferencia')}
                            className={`flex items-center gap-3 rounded-2xl border p-4 text-left transition-all cursor-pointer ${
                                canalPagoReserva === 'transferencia'
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-700 ring-2 ring-emerald-500/20 dark:text-emerald-300'
                                    : 'border-border bg-background text-foreground hover:border-emerald-500/50'
                            }`}
                        >
                            <Building2 className="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            <div className="grow min-w-0">
                                <span className="block text-xs font-black">
                                    Transferencia Bancaria
                                </span>
                                <span className="block text-[11px] text-muted-foreground truncate">
                                    Registro de comprobante manual
                                </span>
                            </div>
                            {canalPagoReserva === 'transferencia' && (
                                <CheckCircle2 className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            )}
                        </button>
                    </div>

                    {canalPagoReserva === 'stripe' && (
                        <div className="flex flex-col gap-3 rounded-3xl border border-border/80 bg-background p-5 shadow-2xs">
                            <div className="flex items-center gap-2">
                                <CreditCard className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                                <span className="text-xs font-black text-foreground">
                                    Pago seguro procesado por Stripe
                                </span>
                            </div>

                            {preparandoStripe && (
                                <p className="text-xs font-semibold text-muted-foreground">
                                    Cargando pasarela de pago seguro...
                                </p>
                            )}

                            {errorStripe && (
                                <Alert variant="destructive">
                                    <AlertDescription className="text-xs font-semibold">
                                        {errorStripe}
                                    </AlertDescription>
                                </Alert>
                            )}

                            {!stripePago && !preparandoStripe && !errorStripe && (
                                <p className="text-xs text-muted-foreground">
                                    Al presionar el botón de confirmación se desplegarán las casillas de tarjeta encriptada.
                                </p>
                            )}

                            <div
                                id="stripe-reserva-payment-element"
                                className={stripePago ? 'min-h-24' : 'min-h-0'}
                            />
                        </div>
                    )}

                    {canalPagoReserva === 'transferencia' && (
                        <div className="flex flex-col gap-2.5 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-5 text-xs text-foreground">
                            <div className="flex items-center gap-2 font-black text-emerald-700 dark:text-emerald-300">
                                <CheckCircle2 className="size-4" />
                                <span>Instrucciones para Transferencia Bancaria</span>
                            </div>
                            <p className="text-muted-foreground">
                                Transfiera la garantía (${formatearNumero(montoGarantia)}) a la cuenta del hotel. Su reservación se guardará inmediatamente en el sistema.
                            </p>
                            <div className="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-2xl border border-border/60 bg-card p-3.5 font-mono text-[11px]">
                                <div>
                                    <strong className="text-muted-foreground">Banco:</strong> BAC Credomatic
                                </div>
                                <div>
                                    <strong className="text-muted-foreground">Cuenta:</strong> 360-984-123456
                                </div>
                                <div>
                                    <strong className="text-muted-foreground">Titular:</strong> Hotel Bugambilias S.A.
                                </div>
                                <div>
                                    <strong className="text-muted-foreground">Concepto:</strong> Reserva {nombreCliente}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}

            <div className="flex items-center justify-between rounded-2xl border border-border/80 bg-background p-3.5 text-xs">
                <div className="flex items-center gap-2 text-muted-foreground font-medium">
                    <Info className="size-4 text-bugambilia-600 dark:text-bugambilia-400 shrink-0" />
                    <span>Conozca nuestras políticas de reserva sin sorpresas.</span>
                </div>
                <button
                    type="button"
                    onClick={() => setModalCondicionesAbierto(true)}
                    className="font-black text-bugambilia-600 hover:underline shrink-0 text-[11px] cursor-pointer dark:text-bugambilia-400"
                >
                    Ver condiciones de pago y cancelación
                </button>
            </div>

            <Alert className="border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-300">
                <ShieldCheck className="size-5 text-emerald-600 dark:text-emerald-400" />
                <AlertDescription className="pl-2 text-xs font-medium">
                    Su reserva será procesada con confirmación inmediata y garantía de satisfacción Hotel Bugambilias.
                </AlertDescription>
            </Alert>

            {/* Modal de Condiciones de Pago y Cancelación */}
            <ModalCondicionesPagoCancelacion
                estaAbierto={modalCondicionesAbierto}
                alCerrar={() => setModalCondicionesAbierto(false)}
            />
        </div>
    );
}

