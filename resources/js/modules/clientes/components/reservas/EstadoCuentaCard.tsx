import { CheckCircle2, Clock } from 'lucide-react';
import type { PortalReservaDetalleCompleto } from '../../types';

interface EstadoCuentaCardProps {
    reserva: PortalReservaDetalleCompleto;
}

export const EstadoCuentaCard = ({ reserva }: EstadoCuentaCardProps) => {
    const { cuenta } = reserva;
    const simbolo = reserva.moneda?.simbolo || '$';
    const totalNum = Number(reserva.total || 0);
    const totalPagadoNum = Number(reserva.total_pagado || 0);
    const saldoNum = Number(reserva.saldo || 0);
    const consumos = Array.isArray(cuenta?.consumos) ? cuenta.consumos : [];

    return (
        <div className="space-y-6 rounded-3xl border border-border/70 bg-card p-6 shadow-xs sm:p-8">
            <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/50 pb-4">
                <div>
                    <span className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                        Estado de Cuenta y Balance
                    </span>
                    <h3 className="text-lg font-black text-foreground">
                        {cuenta?.numero_cuenta
                            ? `Cuenta ${cuenta.numero_cuenta}`
                            : 'Cuenta de Hospedaje'}
                    </h3>
                </div>

                {saldoNum > 0 ? (
                    <div className="flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3.5 py-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                        <Clock className="size-3.5" />
                        <span>
                            Saldo Pendiente: {simbolo}
                            {saldoNum.toFixed(2)}
                        </span>
                    </div>
                ) : (
                    <div className="flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3.5 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 className="size-3.5" />
                        <span>Completamente Liquidado</span>
                    </div>
                )}
            </div>

            {/* Desglose de Totales */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div className="rounded-2xl border border-border/30 bg-secondary/40 p-4">
                    <span className="block text-xs text-muted-foreground">
                        Total Hospedaje
                    </span>
                    <span className="mt-1 block text-xl font-black text-foreground">
                        {simbolo}
                        {totalNum.toFixed(2)}
                    </span>
                </div>

                <div className="rounded-2xl border border-border/30 bg-secondary/40 p-4">
                    <span className="block text-xs text-muted-foreground">
                        Total Abonado
                    </span>
                    <span className="mt-1 block text-xl font-black text-emerald-600 dark:text-emerald-400">
                        {simbolo}
                        {totalPagadoNum.toFixed(2)}
                    </span>
                </div>

                <div className="rounded-2xl border border-border/30 bg-secondary/40 p-4">
                    <span className="block text-xs text-muted-foreground">
                        Saldo por Pagar
                    </span>
                    <span className="mt-1 block text-xl font-black text-foreground">
                        {simbolo}
                        {saldoNum.toFixed(2)}
                    </span>
                </div>
            </div>

            {/* Consumos / Servicios Adicionales en la Cuenta */}
            {consumos.length > 0 && (
                <div className="space-y-3 pt-2">
                    <h4 className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                        Consumos y Servicios Cargados a la Habitación
                    </h4>
                    <div className="divide-y divide-border/40 overflow-hidden rounded-2xl border border-border/60 bg-background/50">
                        {consumos.map((c) => {
                            const consumoTotal = Number(c.total || 0);

                            return (
                                <div
                                    key={c.id}
                                    className="flex items-center justify-between p-3.5 text-xs"
                                >
                                    <div>
                                        <span className="block font-bold text-foreground">
                                            {c.concepto}
                                        </span>
                                        {c.descripcion && (
                                            <span className="block text-[11px] text-muted-foreground">
                                                {c.descripcion}
                                            </span>
                                        )}
                                        <span className="text-[11px] text-muted-foreground">
                                            Cant: {c.cantidad} ·{' '}
                                            {c.created_at || 'Registrado'}
                                        </span>
                                    </div>
                                    <span className="font-mono font-bold text-foreground">
                                        {simbolo}
                                        {consumoTotal.toFixed(2)}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
};
