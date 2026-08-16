import { Link } from '@inertiajs/react';
import {
    Calendar,
    Users,
    CheckCircle2,
    Clock,
    X,
    FileText,
    UserPlus,
} from 'lucide-react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';

interface PropiedadesTarjetaReservaPortalItem {
    reserva: ReservaClienteDomain;
    onSolicitarCancelacion: (reserva: ReservaClienteDomain) => void;
}

export const TarjetaReservaPortalItem = ({
    reserva,
    onSolicitarCancelacion,
}: PropiedadesTarjetaReservaPortalItem) => {
    const esActiva = reserva.estado === 1 || reserva.estado === 2;
    const esCancelada = reserva.estado === 3;
    const monto =
        typeof reserva.total === 'string'
            ? parseFloat(reserva.total) || 0
            : Number(reserva.total) || 0;

    return (
        <Card className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card p-0 font-sans shadow-xs transition-all duration-300 hover:border-bugambilia-500/40 hover:shadow-lg">
            {/* Cabecera con Estado y Código */}
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border/50 bg-muted/30 px-6 py-4">
                <div className="flex items-center gap-2">
                    <span className="text-xs font-black tracking-wider text-muted-foreground uppercase">
                        Código:
                    </span>
                    <span className="font-mono text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                        {reserva.codigo_reserva}
                    </span>
                </div>

                <div className="flex items-center gap-2">
                    {esActiva ? (
                        <Badge className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="mr-1 size-3" />
                            {reserva.estado_label || 'Confirmada'}
                        </Badge>
                    ) : esCancelada ? (
                        <Badge
                            variant="outline"
                            className="border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400"
                        >
                            <X className="mr-1 size-3" />
                            {reserva.estado_label || 'Cancelada'}
                        </Badge>
                    ) : (
                        <Badge
                            variant="outline"
                            className="border-muted bg-muted/50 text-muted-foreground"
                        >
                            <Clock className="mr-1 size-3" />
                            {reserva.estado_label || 'Finalizada'}
                        </Badge>
                    )}
                </div>
            </div>

            {/* Contenido Principal de la Reserva */}
            <div className="flex flex-grow flex-col justify-between gap-4 p-6">
                <div className="space-y-3">
                    <div className="flex items-start justify-between gap-2">
                        <div>
                            <h3 className="text-base font-black text-foreground md:text-lg">
                                {reserva.detalles}
                            </h3>
                            <p className="mt-0.5 text-xs font-semibold text-muted-foreground">
                                Huésped Principal: {reserva.nombre_cliente}
                            </p>
                        </div>

                        <div className="text-right">
                            <span className="text-lg font-black text-foreground">
                                ${formatearNumero(monto)}
                            </span>
                            <span className="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                Pago Confirmado
                            </span>
                        </div>
                    </div>

                    {/* Especificaciones: Fechas & Huéspedes */}
                    <div className="grid grid-cols-1 gap-2 rounded-2xl border border-border/60 bg-background p-3.5 sm:grid-cols-2">
                        <div className="flex items-center gap-2 text-xs font-semibold text-foreground">
                            <Calendar className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                            <div>
                                <span className="block text-[10px] text-muted-foreground">
                                    Check-in / Check-out
                                </span>
                                <span>
                                    {reserva.fecha_check_in}{' '}
                                    {reserva.fecha_check_out
                                        ? `— ${reserva.fecha_check_out}`
                                        : ''}
                                </span>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 text-xs font-semibold text-foreground">
                            <Users className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                            <div>
                                <span className="block text-[10px] text-muted-foreground">
                                    Huéspedes Registrados
                                </span>
                                <span>
                                    {reserva.adultos} Adulto(s){' '}
                                    {reserva.ninos > 0
                                        ? `, ${reserva.ninos} Niño(s)`
                                        : ''}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Acciones de la Reserva */}
                <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border/50 pt-4">
                    <div className="flex flex-wrap items-center gap-2">
                        {/* Auto Check-in Express CTA */}
                        {esActiva && (
                            <Link
                                href={`/reservas/check-in?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                                className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-4 py-2 text-xs font-extrabold text-white shadow-2xs transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                <UserPlus className="size-3.5" />
                                Auto Check-In Express
                            </Link>
                        )}

                        {/* Voucher PDF */}
                        <a
                            href={`/api/reservas/${encodeURIComponent(reserva.codigo_reserva)}/pdf`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3.5 py-2 text-xs font-bold text-foreground transition-colors hover:bg-muted"
                        >
                            <FileText className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            Voucher PDF
                        </a>
                    </div>

                    {/* Botón Cancelar Reserva */}
                    {esActiva && (
                        <button
                            type="button"
                            onClick={() => onSolicitarCancelacion(reserva)}
                            className="cursor-pointer text-xs font-extrabold text-rose-600 transition-colors hover:text-rose-700 hover:underline dark:text-rose-400"
                        >
                            Solicitar Cancelación
                        </button>
                    )}
                </div>
            </div>
        </Card>
    );
};

export default TarjetaReservaPortalItem;
