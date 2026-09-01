import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, Sparkles, XCircle } from 'lucide-react';
import type { ReservaPortalItem } from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';

interface DetalleReservaPortalProps {
    reserva?: ReservaPortalItem;
}

export const DetalleReservaPortal = ({
    reserva,
}: DetalleReservaPortalProps) => {
    if (!reserva) {
        return (
            <>
                <Head>
                    <title>Reserva no encontrada — Hotel Bugambilias</title>
                </Head>
                <div className="mx-auto max-w-3xl px-4 py-16 text-center font-sans">
                    <h2 className="text-xl font-black text-foreground">
                        Reserva no encontrada
                    </h2>
                    <p className="mt-2 text-xs text-muted-foreground">
                        La reserva solicitada no existe o no tienes permisos
                        para visualizarla.
                    </p>
                    <div className="mt-6">
                        <Link
                            href="/mis-reservas"
                            className="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-6 text-xs font-bold text-primary-foreground shadow-md hover:bg-primary/90"
                        >
                            Volver a Mis Reservas
                        </Link>
                    </div>
                </div>
            </>
        );
    }

    const urlVoucher = `/reservas/${reserva.id}/voucher?codigo=${encodeURIComponent(reserva.codigo_reserva)}`;

    const handleCancelarReserva = () => {
        if (
            !confirm(
                '¿Estás seguro de que deseas cancelar tu reserva? El reembolso se procesará de forma automática según las políticas de la habitación.',
            )
        ) {
            return;
        }

        router.post(
            `/reservas/${reserva.id}/cancelar`,
            {
                codigo: reserva.codigo_reserva,
            },
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <>
            <Head>
                <title>
                    Reserva {reserva.codigo_reserva} — Hotel Bugambilias
                </title>
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-8 font-sans sm:px-6 sm:py-12">
                <Link
                    href="/mis-reservas"
                    className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft className="size-4" />
                    <span>Volver a Mis Reservas</span>
                </Link>

                {/* Cabecera */}
                <div className="mt-4 flex flex-col gap-4 rounded-3xl border border-border bg-card p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="font-mono text-2xl font-black text-primary dark:text-rose-400">
                                {reserva.codigo_reserva}
                            </span>
                        </div>
                        <h1 className="mt-1 text-xl font-black text-foreground sm:text-2xl">
                            {reserva.habitacion?.nombre ||
                                reserva.espacio?.nombre ||
                                'Reserva Hotel Bugambilias'}
                        </h1>
                        <p className="text-xs text-muted-foreground">
                            {reserva.habitacion?.categoria} •{' '}
                            {reserva.fecha_check_in} al{' '}
                            {reserva.fecha_check_out}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <a
                            href={urlVoucher}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex h-11 items-center gap-2 rounded-2xl bg-foreground px-4 text-xs font-bold text-background shadow-md transition-all hover:bg-foreground/90"
                        >
                            <Download className="size-4" />
                            <span>Descargar Comprobante PDF</span>
                        </a>

                        {reserva.puede_cancelar && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleCancelarReserva}
                                className="h-11 rounded-2xl border-destructive/30 px-4 text-xs font-bold text-destructive hover:bg-destructive/10 hover:text-destructive"
                            >
                                <XCircle className="mr-1.5 size-4" />
                                <span>Cancelar Reserva</span>
                            </Button>
                        )}
                    </div>
                </div>

                {/* Detalles de Estancia y Desglose Financiero */}
                <div className="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Estancia & Servicios */}
                    <div className="space-y-4 rounded-3xl border border-border bg-card p-6">
                        <h3 className="text-sm font-black text-foreground">
                            Detalles de la Estancia
                        </h3>

                        <div className="space-y-3 text-xs">
                            <div className="flex justify-between border-b border-border/60 pb-2">
                                <span className="text-muted-foreground">
                                    Fecha de Llegada:
                                </span>
                                <span className="font-bold text-foreground">
                                    {reserva.fecha_check_in}
                                </span>
                            </div>
                            <div className="flex justify-between border-b border-border/60 pb-2">
                                <span className="text-muted-foreground">
                                    Fecha de Salida:
                                </span>
                                <span className="font-bold text-foreground">
                                    {reserva.fecha_check_out || '—'}
                                </span>
                            </div>
                            <div className="flex justify-between border-b border-border/60 pb-2">
                                <span className="text-muted-foreground">
                                    Tipo de Reserva:
                                </span>
                                <span className="font-bold text-foreground uppercase">
                                    {reserva.tipo_reserva}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Estado Actual:
                                </span>
                                <span className="font-black text-primary dark:text-rose-400">
                                    {reserva.estado_label || reserva.estado}
                                </span>
                            </div>
                        </div>

                        {reserva.beneficios_aplicados &&
                            reserva.beneficios_aplicados.length > 0 && (
                                <div className="mt-4 border-t border-border/70 pt-4">
                                    <div className="mb-2 text-xs font-bold text-muted-foreground">
                                        Beneficios de Cliente Aplicados:
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {reserva.beneficios_aplicados.map(
                                            (b) => (
                                                <span
                                                    key={b.id}
                                                    className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-700 dark:text-amber-300"
                                                >
                                                    <Sparkles className="size-3.5" />
                                                    <span>
                                                        {b.nombre ||
                                                            'Beneficio Especial'}
                                                    </span>
                                                </span>
                                            ),
                                        )}
                                    </div>
                                </div>
                            )}
                    </div>

                    {/* Desglose de Pago */}
                    <div className="space-y-4 rounded-3xl border border-border bg-card p-6">
                        <h3 className="text-sm font-black text-foreground">
                            Resumen de Pagos
                        </h3>

                        <div className="space-y-3 text-xs">
                            <div className="flex justify-between border-b border-border/60 pb-2">
                                <span className="text-muted-foreground">
                                    Total de la Reserva:
                                </span>
                                <span className="font-black text-foreground">
                                    {reserva.moneda || '$'}
                                    {Number(reserva.total).toFixed(2)}
                                </span>
                            </div>
                            <div className="flex justify-between border-b border-border/60 pb-2">
                                <span className="text-muted-foreground">
                                    Monto Pagado:
                                </span>
                                <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                    {reserva.moneda || '$'}
                                    {Number(reserva.total_pagado).toFixed(2)}
                                </span>
                            </div>
                            <div className="flex justify-between pt-1">
                                <span className="text-muted-foreground">
                                    Saldo Pendiente:
                                </span>
                                <span className="text-base font-black text-primary dark:text-rose-400">
                                    {reserva.moneda || '$'}
                                    {Number(reserva.saldo).toFixed(2)}
                                </span>
                            </div>
                        </div>

                        {reserva.saldo > 0 && (
                            <div className="mt-4 border-t border-border/70 pt-4">
                                <Link
                                    href={`/reservas/${reserva.id}/pago?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                                    className="flex h-11 w-full items-center justify-center rounded-2xl bg-primary text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                >
                                    <span>
                                        Pagar Saldo Pendiente con Tarjeta
                                    </span>
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
};

export default DetalleReservaPortal;
