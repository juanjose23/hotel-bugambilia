import { Head, Link } from '@inertiajs/react';
import { ShieldCheck, ArrowLeft, Loader2 } from 'lucide-react';
import { StripePaymentForm } from '@/modules/reservas/components/StripePaymentForm';
import { usePagoReserva } from '@/modules/reservas/hooks/usePagoReserva';

interface PagoPageProps {
    datosReserva?: {
        id: number;
        codigoReserva: string;
        habitacion: string;
        ubicacion: string;
        fechaEntrada: string;
        fechaSalida: string;
        noches: number;
        huespedes: number;
        precioHabitacion: number;
        impuestos: number;
        total: number;
        totalPagado?: number;
        saldo?: number;
        monedaCodigo?: string;
    } | null;
    serviciosExtras?: {
        id: string;
        nombre: string;
        descripcion: string;
        precio: number;
        moneda: string;
    }[];
}

export const Pago = ({ datosReserva }: PagoPageProps) => {
    const {
        stripeData,
        cargandoIntento,
        errorIntento,
        pagoCompletado,
        handleStripeSuccess,
    } = usePagoReserva(datosReserva);

    if (!datosReserva) {
        return (
            <>
                <Head>
                    <title>Pago de Reserva — Hotel Bugambilias</title>
                </Head>
                <div className="mx-auto max-w-3xl px-4 py-16 text-center font-sans">
                    <h2 className="text-xl font-black text-foreground">
                        No hay reserva seleccionada para pago
                    </h2>
                    <div className="mt-6">
                        <Link
                            href="/habitaciones"
                            className="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-6 text-xs font-bold text-primary-foreground shadow-md hover:bg-primary/90"
                        >
                            Ver Habitaciones
                        </Link>
                    </div>
                </div>
            </>
        );
    }

    const moneda = datosReserva.monedaCodigo || '$';

    return (
        <>
            <Head>
                <title>
                    Pago Seguro de Reserva {datosReserva.codigoReserva} — Hotel
                    Bugambilias
                </title>
            </Head>

            <div className="mx-auto max-w-3xl px-4 py-8 font-sans sm:px-6 sm:py-12">
                <Link
                    href={`/mis-reservas?codigo=${encodeURIComponent(datosReserva.codigoReserva)}`}
                    className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft className="size-4" />
                    <span>Volver a la Reserva</span>
                </Link>

                <div className="mt-4 text-center">
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                        <ShieldCheck className="size-3.5" />
                        <span>Pasarela de Pago Seguro</span>
                    </span>
                    <h1 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        Pago de Reserva #{datosReserva.codigoReserva}
                    </h1>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {datosReserva.habitacion} • {datosReserva.noches}{' '}
                        {datosReserva.noches === 1 ? 'noche' : 'noches'}
                    </p>
                </div>

                {pagoCompletado ? (
                    <div className="mt-8 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-8 text-center">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500 text-white">
                            ✓
                        </div>
                        <h3 className="mt-4 text-lg font-black text-foreground">
                            ¡Pago Procesado y Confirmado Exitosamente!
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Tu saldo ha sido abonado a la cuenta de la reserva #
                            {datosReserva.codigoReserva}.
                        </p>
                        <div className="mt-6 flex justify-center gap-3">
                            <a
                                href={`/reservas/${datosReserva.id}/voucher?codigo=${encodeURIComponent(datosReserva.codigoReserva)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex h-11 items-center justify-center rounded-2xl bg-foreground px-5 text-xs font-bold text-background shadow-md hover:bg-foreground/90"
                            >
                                Descargar Comprobante PDF
                            </a>
                            <Link
                                href="/mis-reservas"
                                className="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-5 text-xs font-bold text-primary-foreground shadow-md hover:bg-primary/90"
                            >
                                Ir a Mis Reservas
                            </Link>
                        </div>
                    </div>
                ) : (
                    <div className="mt-8 space-y-6">
                        {/* Resumen de Estancia */}
                        <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                            <div className="flex justify-between border-b border-border/70 pb-3 text-xs">
                                <span className="text-muted-foreground">
                                    Suite
                                </span>
                                <span className="font-bold text-foreground">
                                    {datosReserva.habitacion}
                                </span>
                            </div>
                            <div className="flex justify-between border-b border-border/70 py-3 text-xs">
                                <span className="text-muted-foreground">
                                    Fechas
                                </span>
                                <span className="font-bold text-foreground">
                                    {datosReserva.fechaEntrada} —{' '}
                                    {datosReserva.fechaSalida}
                                </span>
                            </div>
                            <div className="flex justify-between border-b border-border/70 py-3 text-xs">
                                <span className="text-muted-foreground">
                                    Total de la Estancia
                                </span>
                                <span className="font-bold text-foreground">
                                    {moneda}{' '}
                                    {Number(datosReserva.total).toLocaleString(
                                        'en-US',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        },
                                    )}
                                </span>
                            </div>
                            {datosReserva.totalPagado !== undefined &&
                                datosReserva.totalPagado > 0 && (
                                    <div className="flex justify-between border-b border-border/70 py-3 text-xs">
                                        <span className="text-muted-foreground">
                                            Monto Abonado Previamente
                                        </span>
                                        <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                            {moneda}{' '}
                                            {Number(
                                                datosReserva.totalPagado,
                                            ).toLocaleString('en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            })}
                                        </span>
                                    </div>
                                )}
                            <div className="flex justify-between pt-3 text-sm font-black">
                                <span>
                                    {datosReserva.totalPagado &&
                                    datosReserva.totalPagado > 0
                                        ? 'Saldo Pendiente a Liquidar'
                                        : 'Total a Pagar Ahora'}
                                </span>
                                <span className="text-primary dark:text-rose-400">
                                    {moneda}{' '}
                                    {Number(
                                        datosReserva.saldo !== undefined &&
                                            datosReserva.saldo > 0
                                            ? datosReserva.saldo
                                            : datosReserva.total,
                                    ).toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    })}
                                </span>
                            </div>
                        </div>

                        {/* Formulario Stripe */}
                        <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                            {cargandoIntento && (
                                <div className="flex flex-col items-center justify-center gap-2 py-8 text-muted-foreground">
                                    <Loader2 className="size-6 animate-spin text-primary" />
                                    <span className="text-xs font-bold">
                                        Conectando con Stripe...
                                    </span>
                                </div>
                            )}

                            {errorIntento && (
                                <div className="rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-xs font-bold text-destructive">
                                    {errorIntento}
                                </div>
                            )}

                            {stripeData && (
                                <StripePaymentForm
                                    stripeData={stripeData}
                                    onSuccess={handleStripeSuccess}
                                    onError={() => {}}
                                />
                            )}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
};

export default Pago;
