import { Link } from '@inertiajs/react';
import {
    Calendar,
    Users,
    ChevronRight,
    FileText,
    UtensilsCrossed,
} from 'lucide-react';
import { Button, buttonVariants } from '@/modules/shared/components/ui/button';
import type { PortalReservaResumen } from '../../types';

interface PortalReservaItemProps {
    reserva: PortalReservaResumen;
    onCancelar?: (id: number, codigo: string) => void;
}

export const PortalReservaItem = ({
    reserva,
    onCancelar,
}: PortalReservaItemProps) => {
    const resolverBadgeEstado = () => {
        const est = reserva.estado_label.toLowerCase();

        if (est.includes('confirmada')) {
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        }

        if (est.includes('pendiente')) {
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
        }

        if (est.includes('cancelada')) {
            return 'bg-destructive/10 text-destructive border-destructive/20';
        }

        return 'bg-secondary text-muted-foreground border-border';
    };

    return (
        <div className="flex flex-col justify-between gap-5 rounded-3xl border border-border/70 bg-card p-6 shadow-xs transition-all hover:border-primary/30 sm:p-7">
            {/* Header de la tarjeta */}
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border/50 pb-4">
                <div className="flex items-center gap-2.5">
                    <span className="font-mono text-sm font-bold text-foreground">
                        {reserva.codigo_reserva}
                    </span>
                    <span
                        className={`rounded-full border px-2.5 py-0.5 text-xs font-bold ${resolverBadgeEstado()}`}
                    >
                        {reserva.estado_label}
                    </span>
                </div>

                <div className="text-right">
                    <span className="block text-xs text-muted-foreground">
                        Total Estancia
                    </span>
                    <span className="text-lg font-black text-foreground">
                        {reserva.moneda_simbolo}
                        {reserva.total.toFixed(2)}
                    </span>
                </div>
            </div>

            {/* Información central */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <span className="block text-xs text-muted-foreground">
                        Habitación / Recurso
                    </span>
                    <h4 className="mt-0.5 font-bold text-foreground">
                        {reserva.recurso.nombre}
                    </h4>
                    <span className="text-xs text-muted-foreground">
                        {reserva.recurso.categoria}
                    </span>
                </div>

                <div>
                    <span className="block text-xs text-muted-foreground">
                        Fechas de Estancia
                    </span>
                    <div className="mt-1 flex items-center gap-1.5 text-xs font-medium text-foreground">
                        <Calendar className="size-3.5 text-primary" />
                        <span>
                            {reserva.fecha_check_in || 'N/D'} al{' '}
                            {reserva.fecha_check_out || 'N/D'}
                        </span>
                    </div>
                    <span className="text-xs text-muted-foreground">
                        {reserva.noches} noche{reserva.noches > 1 ? 's' : ''}
                    </span>
                </div>

                <div>
                    <span className="block text-xs text-muted-foreground">
                        Huéspedes
                    </span>
                    <div className="mt-1 flex items-center gap-1.5 text-xs font-medium text-foreground">
                        <Users className="size-3.5 text-primary" />
                        <span>
                            {reserva.adultos} adulto
                            {reserva.adultos > 1 ? 's' : ''}
                            {reserva.ninos > 0
                                ? `, ${reserva.ninos} niños`
                                : ''}
                        </span>
                    </div>
                    {reserva.saldo > 0 ? (
                        <span className="text-xs font-semibold text-amber-600 dark:text-amber-400">
                            Saldo pendiente: {reserva.moneda_simbolo}
                            {reserva.saldo.toFixed(2)}
                        </span>
                    ) : (
                        <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            Pagado 100%
                        </span>
                    )}
                </div>
            </div>

            {/* Footer con acciones */}
            <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border/40 pt-2">
                <div className="flex flex-wrap gap-2">
                    <Link
                        href={`/portal/reservas/${reserva.id}/servicios`}
                        className={buttonVariants({
                            variant: 'outline',
                            size: 'sm',
                            className:
                                'gap-1.5 rounded-xl text-xs font-semibold',
                        })}
                    >
                        <UtensilsCrossed className="size-3.5 text-primary" />
                        <span>Solicitar Servicios</span>
                    </Link>
                    <a
                        href={reserva.url_voucher}
                        target="_blank"
                        rel="noreferrer"
                        className={buttonVariants({
                            variant: 'outline',
                            size: 'sm',
                            className:
                                'gap-1.5 rounded-xl text-xs font-semibold',
                        })}
                    >
                        <FileText className="size-3.5 text-primary" />
                        <span>Comprobante PDF</span>
                    </a>
                </div>

                <div className="flex items-center gap-2">
                    {reserva.puede_cancelar && onCancelar && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() =>
                                onCancelar(reserva.id, reserva.codigo_reserva)
                            }
                            className="text-xs font-bold text-destructive hover:bg-destructive/10"
                        >
                            Cancelar
                        </Button>
                    )}
                    <Link
                        href={`/portal/reservas/${reserva.id}`}
                        className={buttonVariants({
                            size: 'sm',
                            className: 'gap-1 rounded-xl font-bold',
                        })}
                    >
                        <span>Detalle</span>
                        <ChevronRight className="size-4" />
                    </Link>
                </div>
            </div>
        </div>
    );
};
