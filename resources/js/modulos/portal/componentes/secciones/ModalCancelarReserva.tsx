import { X, AlertTriangle, Loader2 } from 'lucide-react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Button } from '@/modulos/compartido/ui/boton';

interface PropiedadesModalCancelarReserva {
    reserva: ReservaClienteDomain | null;
    motivoCancelacion: string;
    onMotivoChange: (value: string) => void;
    onClose: () => void;
    onConfirm: () => void;
    cancelando: boolean;
    errorCancelacion: string | null;
    mensajeCancelacion: string | null;
    reembolsoPendiente: boolean;
}

export const ModalCancelarReserva = ({
    reserva,
    motivoCancelacion,
    onMotivoChange,
    onClose,
    onConfirm,
    cancelando,
    errorCancelacion,
    mensajeCancelacion,
    reembolsoPendiente,
}: PropiedadesModalCancelarReserva) => {
    if (!reserva) {
        return null;
    }

    return (
        <div className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 font-sans backdrop-blur-sm">
            <div className="relative w-full max-w-lg rounded-3xl border border-border bg-card p-6 shadow-2xl md:p-8">
                <button
                    type="button"
                    onClick={onClose}
                    className="absolute top-4 right-4 flex size-8 items-center justify-center rounded-full bg-muted text-muted-foreground hover:bg-muted/80 hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                <div className="mb-4 flex items-center gap-3">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        <AlertTriangle className="size-6" />
                    </div>
                    <div>
                        <h3 className="text-lg font-black text-foreground">
                            Cancelar Reservación
                        </h3>
                        <p className="text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                            {reserva.codigo_reserva} — {reserva.detalles}
                        </p>
                    </div>
                </div>

                {mensajeCancelacion ? (
                    <div className="space-y-4">
                        <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                            {mensajeCancelacion}
                        </div>

                        {reembolsoPendiente && (
                            <p className="text-xs text-muted-foreground">
                                Su solicitud de reembolso ha sido enviada al
                                departamento administrativo para su aprobación.
                            </p>
                        )}

                        <div className="flex justify-end">
                            <Button
                                onClick={onClose}
                                className="rounded-full bg-bugambilia-600 font-extrabold text-white"
                            >
                                Entendido
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-4">
                        <p className="text-xs font-medium text-muted-foreground">
                            ¿Está seguro de que desea cancelar esta reservación?
                            Esta acción aplicará las políticas de cancelación
                            del hotel.
                        </p>

                        <div>
                            <label className="mb-1.5 block text-xs font-bold text-foreground">
                                Motivo de la cancelación (opcional):
                            </label>
                            <textarea
                                rows={3}
                                value={motivoCancelacion}
                                onChange={(e) => onMotivoChange(e.target.value)}
                                placeholder="Describa brevemente la razón de su cancelación..."
                                className="w-full rounded-2xl border border-border/80 bg-background p-3 text-xs font-medium text-foreground focus:border-bugambilia-500 focus:outline-hidden"
                            />
                        </div>

                        {errorCancelacion && (
                            <div className="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs font-bold text-rose-600 dark:text-rose-400">
                                {errorCancelacion}
                            </div>
                        )}

                        <div className="flex items-center justify-end gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onClose}
                                disabled={cancelando}
                                className="rounded-full font-bold"
                            >
                                Mantener Reserva
                            </Button>
                            <Button
                                type="button"
                                onClick={onConfirm}
                                disabled={cancelando}
                                className="rounded-full bg-rose-600 font-extrabold text-white hover:bg-rose-700"
                            >
                                {cancelando ? (
                                    <>
                                        <Loader2 className="mr-2 size-4 animate-spin" />
                                        Procesando...
                                    </>
                                ) : (
                                    'Confirmar Cancelación'
                                )}
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ModalCancelarReserva;
