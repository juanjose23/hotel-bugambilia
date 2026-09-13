import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Download } from 'lucide-react';
import { useCancelarReserva } from '@/modules/reservas/hooks/useCancelarReserva';
import type { ReservaPortalItem } from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';

interface DetalleReservaPortalProps {
    reserva?: ReservaPortalItem;
}

export const DetalleReservaPortal = ({
    reserva,
}: DetalleReservaPortalProps) => {
    const { cancelarReserva } = useCancelarReserva({
        confirmMessage:
            '¿Estás seguro de que deseas cancelar esta reserva? El reembolso se calculará y procesará de forma automática según la política de cancelación.',
    });

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
        cancelarReserva(reserva.id, reserva.codigo_reserva);
    };

    return (
        <>
            <Head>
                <title>
                    Reserva {reserva.codigo_reserva} — Hotel Bugambilias
                </title>
            </Head>
            <div className="mx-auto max-w-3xl px-4 py-8 font-sans">
                <Link
                    href="/mis-reservas"
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-3.5" />
                    <span>Volver a Mis Reservas</span>
                </Link>

                <h4 className="mt-4 flex flex-wrap items-center gap-2 text-lg font-black text-foreground">
                    Reserva {reserva.codigo_reserva}
                    {reserva.estado && (
                        <span className="rounded-full border border-border bg-muted/60 px-2.5 py-0.5 text-[10px] font-black tracking-wider text-muted-foreground uppercase">
                            {reserva.estado}
                        </span>
                    )}
                </h4>

                <div className="mt-6 space-y-2 text-xs text-muted-foreground">
                    <p>
                        Fecha de entrada: {reserva.fecha_check_in ?? '—'} —
                        Fecha de salida: {reserva.fecha_check_out ?? '—'}
                    </p>
                    <p>
                        Total:
                        {(reserva.total ?? 0).toLocaleString('es-MX', {
                            style: 'currency',
                            currency: reserva.moneda ?? 'MXN',
                        })}
                    </p>
                </div>

                <div className="mt-8 flex flex-wrap items-center gap-3">
                    <Link
                        href={urlVoucher}
                        className="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-6 text-xs font-bold text-primary-foreground shadow-md hover:bg-primary/90"
                    >
                        <Download className="mr-2 size-4" />
                        Descargar comprobante
                    </Link>

                    <Button
                        type="button"
                        variant="destructive"
                        onClick={handleCancelarReserva}
                        className="h-11 px-6 text-xs font-bold"
                    >
                        Cancelar reserva
                    </Button>
                </div>
            </div>
        </>
    );
};

export default DetalleReservaPortal;
