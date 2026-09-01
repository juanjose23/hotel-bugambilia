import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    FileText,
    UtensilsCrossed,
    Users,
    AlertCircle,
} from 'lucide-react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { DetalleSuiteCard } from '@/modules/clientes/components/reservas/DetalleSuiteCard';
import { EstadoCuentaCard } from '@/modules/clientes/components/reservas/EstadoCuentaCard';
import type { PortalReservaDetalleCompleto } from '@/modules/clientes/types';
import { Button, buttonVariants } from '@/modules/shared/components/ui/button';

interface ReservaDetalleProps {
    reserva: PortalReservaDetalleCompleto;
}

export const ReservaDetalle = ({ reserva }: ReservaDetalleProps) => {
    const handleCancelarReserva = () => {
        if (
            !confirm(
                '¿Estás seguro de que deseas cancelar esta reserva? El reembolso se calculará y procesará de forma automática según la política de cancelación.',
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
        <PortalLayout>
            <Head>
                <title>{`Reserva #${reserva.codigo_reserva} — Portal de Huéspedes`}</title>
                <meta
                    name="description"
                    content={`Detalle de la reserva ${reserva.codigo_reserva} en Hotel Bugambilias Estelí.`}
                />
            </Head>

            <div className="mx-auto max-w-5xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Cabecera y Navegación de retorno */}
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/60 pb-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/portal/reservas"
                            className={buttonVariants({
                                variant: 'ghost',
                                size: 'icon',
                                className: 'rounded-xl',
                            })}
                        >
                            <ArrowLeft className="size-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-mono text-xs font-bold text-primary">
                                    #{reserva.codigo_reserva}
                                </span>
                                <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                                    {reserva.estado_label}
                                </span>
                            </div>
                            <h1 className="mt-0.5 text-xl font-black text-foreground sm:text-2xl">
                                {reserva.recurso.nombre}
                            </h1>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <a
                            href={reserva.url_voucher}
                            target="_blank"
                            rel="noreferrer"
                            className={buttonVariants({
                                variant: 'outline',
                                className: 'gap-2 rounded-xl text-xs font-bold',
                            })}
                        >
                            <FileText className="size-4 text-primary" />
                            <span>Descargar Voucher PDF</span>
                        </a>

                        <Link
                            href={`/portal/reservas/${reserva.id}/servicios`}
                            className={buttonVariants({
                                className:
                                    'gap-2 rounded-xl text-xs font-bold shadow-sm',
                            })}
                        >
                            <UtensilsCrossed className="size-4" />
                            <span>Pedir Servicios</span>
                        </Link>
                    </div>
                </div>

                {/* Grid de contenido */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    {/* Columna Principal (2 columnas) */}
                    <div className="space-y-8 lg:col-span-2">
                        <DetalleSuiteCard reserva={reserva} />
                        <EstadoCuentaCard reserva={reserva} />
                    </div>

                    {/* Barra lateral de información y acompañantes (1 columna) */}
                    <div className="space-y-6">
                        {/* Tarjeta de fechas y huésped titular */}
                        <div className="space-y-4 rounded-3xl border border-border/70 bg-card p-6 shadow-xs">
                            <h4 className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                Datos de la Reserva
                            </h4>

                            <div className="space-y-3 text-xs">
                                <div>
                                    <span className="block text-muted-foreground">
                                        Huésped Titular
                                    </span>
                                    <strong className="block text-sm font-bold text-foreground">
                                        {reserva.nombre_cliente}
                                    </strong>
                                    <span className="text-muted-foreground">
                                        {reserva.email_cliente}
                                    </span>
                                </div>

                                <div className="border-t border-border/40 pt-2.5">
                                    <span className="block text-muted-foreground">
                                        Estancia
                                    </span>
                                    <div className="mt-0.5 flex items-center gap-1.5 font-bold text-foreground">
                                        <Calendar className="size-3.5 text-primary" />
                                        <span>
                                            {reserva.fecha_check_in || 'N/D'} →{' '}
                                            {reserva.fecha_check_out || 'N/D'}
                                        </span>
                                    </div>
                                    <span className="text-[11px] text-muted-foreground">
                                        {reserva.noches} noche
                                        {reserva.noches > 1 ? 's' : ''}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Tarjeta de Acompañantes */}
                        <div className="space-y-4 rounded-3xl border border-border/70 bg-card p-6 shadow-xs">
                            <div className="flex items-center justify-between">
                                <h4 className="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    Acompañantes ({reserva.acompanantes.length})
                                </h4>
                                <Link
                                    href={`/portal/reservas/${reserva.id}/acompanantes`}
                                    className={buttonVariants({
                                        variant: 'ghost',
                                        size: 'sm',
                                        className:
                                            'h-7 text-xs font-bold text-primary',
                                    })}
                                >
                                    Editar
                                </Link>
                            </div>

                            {reserva.acompanantes.length > 0 ? (
                                <div className="space-y-2">
                                    {reserva.acompanantes.map((ac, idx) => (
                                        <div
                                            key={idx}
                                            className="flex items-center justify-between rounded-xl bg-secondary/40 px-3.5 py-2.5 text-xs"
                                        >
                                            <div className="flex items-center gap-2">
                                                <Users className="size-3.5 text-muted-foreground" />
                                                <span className="font-bold text-foreground">
                                                    {ac.nombre}
                                                </span>
                                            </div>
                                            <span className="text-[11px] text-muted-foreground capitalize">
                                                {ac.tipo}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-xs leading-relaxed text-muted-foreground">
                                    No has registrado acompañantes aún.
                                    Agrégalos para agilizar el registro al
                                    llegar al hotel.
                                </p>
                            )}

                            <Link
                                href={`/portal/reservas/${reserva.id}/acompanantes`}
                                className={buttonVariants({
                                    variant: 'outline',
                                    size: 'sm',
                                    className:
                                        'w-full gap-1.5 rounded-xl text-xs font-bold',
                                })}
                            >
                                <Users className="size-3.5 text-primary" />
                                <span>Gestionar Huéspedes</span>
                            </Link>
                        </div>

                        {/* Cancelación */}
                        {reserva.puede_cancelar && (
                            <div className="space-y-3 rounded-3xl border border-destructive/20 bg-destructive/5 p-5 text-center">
                                <div className="flex items-center justify-center gap-1.5 text-xs font-bold text-destructive">
                                    <AlertCircle className="size-4" />
                                    <span>¿Necesitas cancelar?</span>
                                </div>
                                <p className="text-[11px] leading-relaxed text-muted-foreground">
                                    Si tus planes cambiaron, puedes cancelar tu
                                    reservación conforme a las políticas del
                                    hotel.
                                </p>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    onClick={handleCancelarReserva}
                                    className="w-full rounded-xl text-xs font-bold"
                                >
                                    Cancelar esta Reserva
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </PortalLayout>
    );
};

ReservaDetalle.layout = (page: React.ReactNode) => page;

export default ReservaDetalle;
