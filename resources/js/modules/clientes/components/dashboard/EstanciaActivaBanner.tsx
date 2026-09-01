import { Link } from '@inertiajs/react';
import {
    Calendar,
    ArrowRight,
    Sparkles,
    FileText,
    UtensilsCrossed,
    Users,
} from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';
import type { PortalReservaResumen } from '../../types';

interface EstanciaActivaBannerProps {
    reserva: PortalReservaResumen;
}

export const EstanciaActivaBanner = ({
    reserva,
}: EstanciaActivaBannerProps) => {
    return (
        <div className="relative overflow-hidden rounded-3xl border border-primary/20 bg-gradient-to-br from-primary/10 via-card to-card p-6 shadow-sm sm:p-8">
            <div className="flex flex-col justify-between gap-6 md:flex-row md:items-center">
                {/* Información Principal */}
                <div className="max-w-xl space-y-4">
                    <div className="flex flex-wrap items-center gap-2.5">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/20 px-3 py-1 text-xs font-black text-primary">
                            <Sparkles className="size-3.5" />
                            <span>Estancia Activa</span>
                        </span>
                        <span className="rounded-full border border-border/80 bg-background/80 px-3 py-1 font-mono text-xs font-bold text-foreground">
                            {reserva.codigo_reserva}
                        </span>
                        <span className="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            {reserva.estado_label}
                        </span>
                    </div>

                    <div>
                        <h3 className="text-xl font-black text-foreground sm:text-2xl">
                            {reserva.recurso.nombre}
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            {reserva.recurso.categoria} · {reserva.noches} noche
                            {reserva.noches > 1 ? 's' : ''} de confort y
                            descanso
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-4 text-xs font-semibold text-muted-foreground sm:gap-6">
                        <div className="flex items-center gap-1.5">
                            <Calendar className="size-4 text-primary" />
                            <span>
                                Check-in:{' '}
                                <strong className="text-foreground">
                                    {reserva.fecha_check_in || 'N/D'}
                                </strong>
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <Calendar className="size-4 text-primary" />
                            <span>
                                Check-out:{' '}
                                <strong className="text-foreground">
                                    {reserva.fecha_check_out || 'N/D'}
                                </strong>
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <Users className="size-4 text-primary" />
                            <span>
                                <strong className="text-foreground">
                                    {reserva.adultos}
                                </strong>{' '}
                                adultos
                                {reserva.ninos > 0
                                    ? `, ${reserva.ninos} niños`
                                    : ''}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Acciones Rápidas */}
                <div className="flex shrink-0 flex-col gap-2.5 sm:flex-row md:flex-col">
                    <Link
                        href={`/portal/reservas/${reserva.id}`}
                        className={buttonVariants({
                            size: 'lg',
                            className:
                                'gap-2 rounded-2xl font-bold shadow-md shadow-primary/20',
                        })}
                    >
                        <span>Ver Detalles</span>
                        <ArrowRight className="size-4" />
                    </Link>

                    <div className="flex gap-2">
                        <Link
                            href={`/portal/reservas/${reserva.id}/servicios`}
                            className={buttonVariants({
                                variant: 'outline',
                                size: 'sm',
                                className:
                                    'flex-1 gap-1.5 rounded-xl text-xs font-semibold',
                            })}
                        >
                            <UtensilsCrossed className="size-3.5 text-primary" />
                            <span>Servicios</span>
                        </Link>
                        <a
                            href={reserva.url_voucher}
                            target="_blank"
                            rel="noreferrer"
                            className={buttonVariants({
                                variant: 'outline',
                                size: 'sm',
                                className:
                                    'flex-1 gap-1.5 rounded-xl text-xs font-semibold',
                            })}
                        >
                            <FileText className="size-3.5 text-primary" />
                            <span>Voucher PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    );
};
