import { Link } from '@inertiajs/react';
import {
    CheckCircle2,
    Download,
    Copy,
    Check,
    Calendar,
    ArrowRight,
    ShieldCheck,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import type { ReservaCreadaResponse } from '../types';

interface ReservaConfirmadaModalProps {
    reserva: ReservaCreadaResponse;
    onClose: () => void;
}

export const ReservaConfirmadaModal = ({
    reserva,
    onClose,
}: ReservaConfirmadaModalProps) => {
    const [copiado, setCopiado] = useState(false);

    const copiarCodigo = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(reserva.codigo_reserva);
            setCopiado(true);
            setTimeout(() => setCopiado(false), 2000);
        }
    };

    const urlVoucher = `/reservas/${reserva.id}/voucher?codigo=${encodeURIComponent(reserva.codigo_reserva)}`;

    return (
        <div className="space-y-6 text-center font-sans">
            {/* Ícono de Éxito */}
            <div className="mx-auto flex size-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                <CheckCircle2 className="animate-in zoom-in-75 size-10 duration-300" />
            </div>

            <div>
                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                    <ShieldCheck className="size-3.5" />
                    <span>¡Reserva Confirmada con Éxito!</span>
                </span>
                <h2 className="mt-2 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                    ¡Te esperamos en Hotel Bugambilias!
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                    Hemos registrado tu estancia. Guarda tu código de
                    confirmación para consultar los detalles o realizar tu
                    Check-in.
                </p>
            </div>

            {/* Tarjeta de Código de Confirmación */}
            <div className="rounded-2xl border border-primary/20 bg-primary/5 p-4 text-center dark:bg-rose-950/20">
                <div className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                    Código de Confirmación
                </div>
                <div className="mt-1 flex items-center justify-center gap-2">
                    <span className="font-mono text-2xl font-black text-primary sm:text-3xl dark:text-rose-400">
                        {reserva.codigo_reserva}
                    </span>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={copiarCodigo}
                        className="rounded-xl shadow-xs transition-colors hover:bg-muted"
                        title="Copiar código"
                    >
                        {copiado ? (
                            <Check className="size-4 text-emerald-600 dark:text-emerald-400" />
                        ) : (
                            <Copy className="size-4" />
                        )}
                    </Button>
                </div>
            </div>

            {/* Resumen Rápido */}
            <div className="space-y-2 rounded-2xl border border-border bg-card p-4 text-left text-xs">
                {reserva.habitacion_nombre && (
                    <div className="flex justify-between border-b border-border/60 pb-2">
                        <span className="text-muted-foreground">
                            Suite / Habitación
                        </span>
                        <span className="font-bold text-foreground">
                            {reserva.habitacion_nombre}
                        </span>
                    </div>
                )}
                {reserva.fecha_check_in && reserva.fecha_check_out && (
                    <div className="flex justify-between border-b border-border/60 pb-2">
                        <span className="text-muted-foreground">
                            Fechas de Estancia
                        </span>
                        <span className="font-bold text-foreground">
                            {reserva.fecha_check_in} — {reserva.fecha_check_out}
                        </span>
                    </div>
                )}
                <div className="flex justify-between pt-1">
                    <span className="text-muted-foreground">
                        Modalidad de Pago
                    </span>
                    <span className="font-black text-primary dark:text-rose-400">
                        {reserva.tipo_pago === 'pago_completo'
                            ? 'Pago Completo Confirmado'
                            : reserva.tipo_pago === 'abono_50'
                              ? 'Abono 50% Confirmado'
                              : 'Pago en Check-in'}
                    </span>
                </div>
            </div>

            {/* Botones de Acción */}
            <div className="flex flex-col gap-2.5 sm:flex-row">
                <a
                    href={urlVoucher}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex h-11 flex-1 items-center justify-center gap-2 rounded-2xl bg-foreground text-xs font-bold text-background shadow-md transition-all hover:bg-foreground/90"
                >
                    <Download className="size-4" />
                    <span>Descargar Comprobante PDF</span>
                </a>

                <Link
                    href={`/mis-reservas?codigo=${encodeURIComponent(reserva.codigo_reserva)}`}
                    className="flex h-11 flex-1 items-center justify-center gap-2 rounded-2xl border border-border bg-card text-xs font-bold text-foreground shadow-xs transition-colors hover:bg-muted"
                >
                    <Calendar className="size-4" />
                    <span>Ver en Mis Reservas</span>
                    <ArrowRight className="size-3.5" />
                </Link>
            </div>

            <Button
                variant="ghost"
                onClick={onClose}
                className="w-full text-xs font-bold text-muted-foreground"
            >
                Cerrar ventana
            </Button>
        </div>
    );
};

export default ReservaConfirmadaModal;
