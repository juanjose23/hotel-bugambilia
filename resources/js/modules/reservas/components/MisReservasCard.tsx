import { Link } from '@inertiajs/react';
import {
    Calendar,
    Download,
    XCircle,
    Clock,
    CheckCircle2,
    Sparkles,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { ReservaPortalItem } from '../types';

interface MisReservasCardProps {
    reserva: ReservaPortalItem;
    onCancelar?: (reservaId: number, codigoReserva?: string) => void;
}

export const MisReservasCard = ({
    reserva,
    onCancelar,
}: MisReservasCardProps) => {
    const resolverBadgeEstado = () => {
        const est = reserva.estado?.toLowerCase() || '';

        if (est.includes('confirmada')) {
            return (
                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-black text-emerald-600 dark:text-emerald-400">
                    <CheckCircle2 className="size-3.5" />
                    <span>Confirmada</span>
                </span>
            );
        }

        if (est.includes('pendiente')) {
            return (
                <span className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-1 text-[11px] font-black text-amber-600 dark:text-amber-400">
                    <Clock className="size-3.5" />
                    <span>Pendiente de Pago</span>
                </span>
            );
        }

        if (est.includes('cancelada')) {
            return (
                <span className="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-[11px] font-black text-destructive">
                    <XCircle className="size-3.5" />
                    <span>Cancelada</span>
                </span>
            );
        }

        return (
            <span className="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-[11px] font-bold text-muted-foreground">
                <span>{reserva.estado_label || reserva.estado}</span>
            </span>
        );
    };

    const urlVoucher = `/reservas/${reserva.id}/voucher?codigo=${encodeURIComponent(reserva.codigo_reserva)}`;

    return (
        <div className="flex flex-col justify-between overflow-hidden rounded-3xl border border-border bg-card p-5 font-sans shadow-xs transition-all hover:border-primary/30 hover:shadow-md sm:p-6">
            <div>
                {/* Cabecera de la Tarjeta */}
                <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border/70 pb-4">
                    <div className="flex items-center gap-2">
                        <span className="font-mono text-sm font-black text-primary sm:text-base dark:text-rose-400">
                            {reserva.codigo_reserva}
                        </span>
                        {resolverBadgeEstado()}
                    </div>
                    <div className="text-[11px] font-medium text-muted-foreground">
                        {reserva.created_at
                            ? `Creada el ${reserva.created_at}`
                            : ''}
                    </div>
                </div>

                {/* Contenido Principal */}
                <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 className="text-base font-black text-foreground sm:text-lg">
                            {reserva.habitacion?.nombre ||
                                reserva.espacio?.nombre ||
                                'Reserva Hotel Bugambilias'}
                        </h4>
                        <div className="mt-1 flex items-center gap-2 text-xs font-medium text-muted-foreground">
                            <Calendar className="size-3.5 text-primary" />
                            <span>
                                {reserva.fecha_check_in}
                                {reserva.fecha_check_out
                                    ? ` al ${reserva.fecha_check_out}`
                                    : ''}
                            </span>
                        </div>
                    </div>

                    {/* Importes */}
                    <div className="rounded-2xl border border-border/60 bg-muted/20 p-3 text-right">
                        <div className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                            Total Estancia
                        </div>
                        <div className="text-lg font-black text-foreground">
                            {reserva.moneda || '$'}
                            {Number(reserva.total).toFixed(2)}
                        </div>
                        {reserva.saldo > 0 ? (
                            <div className="text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                Saldo pendiente: {reserva.moneda || '$'}
                                {Number(reserva.saldo).toFixed(2)}
                            </div>
                        ) : (
                            <div className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                Pagado 100%
                            </div>
                        )}
                    </div>
                </div>

                {/* Servicios y Beneficios */}
                {reserva.beneficios_aplicados &&
                    reserva.beneficios_aplicados.length > 0 && (
                        <div className="mt-4 flex flex-wrap gap-1.5">
                            {reserva.beneficios_aplicados.map((b) => (
                                <span
                                    key={b.id}
                                    className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300"
                                >
                                    <Sparkles className="size-3" />
                                    <span>
                                        {b.nombre || 'Beneficio Exclusivo'}
                                    </span>
                                </span>
                            ))}
                        </div>
                    )}
            </div>

            {/* Acciones */}
            <div className="mt-5 flex flex-wrap items-center justify-between gap-2.5 border-t border-border/70 pt-4">
                <div className="flex gap-2">
                    <a
                        href={urlVoucher}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex h-9 items-center gap-1.5 rounded-xl border border-border bg-background px-3 text-xs font-bold text-foreground shadow-xs transition-colors hover:bg-muted"
                    >
                        <Download className="size-3.5" />
                        <span>Comprobante PDF</span>
                    </a>

                    {reserva.saldo > 0 &&
                        !reserva.estado?.toLowerCase().includes('cancelada') &&
                        !reserva.estado?.toLowerCase().includes('no_show') && (
                            <Link
                                href={`/reservas/${reserva.id}/pago?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                                className="inline-flex h-9 items-center gap-1.5 rounded-xl bg-primary px-3 text-xs font-black text-primary-foreground shadow-xs hover:bg-primary/90"
                            >
                                <span>
                                    Pagar Saldo ({reserva.moneda || '$'}
                                    {Number(reserva.saldo).toFixed(2)})
                                </span>
                            </Link>
                        )}
                </div>

                {reserva.puede_cancelar && onCancelar && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() =>
                            onCancelar(reserva.id, reserva.codigo_reserva)
                        }
                        className="text-xs font-bold text-destructive hover:bg-destructive/10 hover:text-destructive"
                    >
                        Cancelar reserva
                    </Button>
                )}
            </div>
        </div>
    );
};

export default MisReservasCard;
