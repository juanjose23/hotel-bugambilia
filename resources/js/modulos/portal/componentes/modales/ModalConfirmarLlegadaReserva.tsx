import { Clock, CheckCircle2, X, Car, UserPlus } from 'lucide-react';
import { useState } from 'react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesModalConfirmarLlegadaReserva {
    reserva: ReservaClienteDomain | null;
    estaAbierto: boolean;
    alCerrar: () => void;
}

export const ModalConfirmarLlegadaReserva = ({
    reserva,
    estaAbierto,
    alCerrar,
}: PropiedadesModalConfirmarLlegadaReserva) => {
    const [horaEstimada, setHoraEstimada] = useState('14:00');
    const [requiereParqueo, setRequiereParqueo] = useState(false);
    const [notasLlegada, setNotasLlegada] = useState('');
    const [cargando, setCargando] = useState(false);
    const [confirmado, setConfirmado] = useState(false);

    if (!estaAbierto || !reserva) {
        return null;
    }

    const manejarConfirmacion = (e: React.FormEvent) => {
        e.preventDefault();
        setCargando(true);
        setTimeout(() => {
            setCargando(false);
            setConfirmado(true);
        }, 800);
    };

    const resetearYcerrar = () => {
        setConfirmado(false);
        alCerrar();
    };

    return (
        <div className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs duration-200">
            <div className="relative w-full max-w-lg overflow-hidden rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-2xl md:p-8">
                {/* Botón de cierre */}
                <button
                    type="button"
                    onClick={resetearYcerrar}
                    className="absolute top-4 right-4 flex size-8 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                {confirmado ? (
                    <div className="space-y-4 py-6 text-center">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h3 className="text-xl font-black text-foreground">
                            ¡Llegada Confirmada!
                        </h3>
                        <p className="text-xs font-medium text-muted-foreground">
                            Su hora estimada de arribo ({horaEstimada} hrs) para
                            la reserva{' '}
                            <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                {reserva.codigo_reserva}
                            </span>{' '}
                            ha sido notificada a recepción. Su habitación estará
                            lista a su arribo.
                        </p>
                        <div className="pt-2">
                            <Button
                                onClick={resetearYcerrar}
                                className="w-full rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                Entendido
                            </Button>
                        </div>
                    </div>
                ) : (
                    <form onSubmit={manejarConfirmacion} className="space-y-5">
                        <div className="space-y-1">
                            <Badge className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <Clock className="mr-1 size-3" />
                                Check-in Express & Arribo
                            </Badge>
                            <h2 className="text-xl font-black text-foreground md:text-2xl">
                                Confirmar Hora de Llegada
                            </h2>
                            <p className="text-xs font-medium text-muted-foreground">
                                {reserva.detalles} — Check-in programado:{' '}
                                <span className="font-bold text-foreground">
                                    {reserva.fecha_check_in}
                                </span>
                            </p>
                        </div>

                        <div className="space-y-3">
                            <div className="space-y-1">
                                <Label className="text-xs font-bold text-foreground">
                                    Hora Estimada de Llegada al Hotel
                                </Label>
                                <Input
                                    type="time"
                                    value={horaEstimada}
                                    onChange={(e) =>
                                        setHoraEstimada(e.target.value)
                                    }
                                    className="rounded-xl border-border/80 font-mono text-xs font-bold"
                                />
                            </div>

                            <div className="flex items-center gap-3 rounded-2xl border border-border/70 bg-muted/30 p-3.5">
                                <Car className="size-5 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div className="grow">
                                    <span className="block text-xs font-black text-foreground">
                                        ¿Requiere espacio de Parqueo Privado?
                                    </span>
                                    <span className="block text-[11px] font-medium text-muted-foreground">
                                        Estacionamiento vigilado 24/7 sin costo
                                        adicional.
                                    </span>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={requiereParqueo}
                                    onChange={(e) =>
                                        setRequiereParqueo(e.target.checked)
                                    }
                                    className="size-4 cursor-pointer accent-bugambilia-600"
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-bold text-foreground">
                                    Indicaciones de Arribo o Notas de Recepción
                                </Label>
                                <Input
                                    value={notasLlegada}
                                    onChange={(e) =>
                                        setNotasLlegada(e.target.value)
                                    }
                                    placeholder="Ej: Llego en vuelo nocturno, requiero apoyo con equipaje"
                                    className="rounded-xl border-border/80 text-xs"
                                />
                            </div>
                        </div>

                        <div className="flex items-center gap-2 border-t border-border/60 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetearYcerrar}
                                className="flex-1 rounded-full text-xs font-bold"
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="submit"
                                disabled={cargando}
                                className="flex-1 rounded-full bg-bugambilia-600 text-xs font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                <UserPlus className="mr-1 size-3.5" />
                                {cargando
                                    ? 'Notificando...'
                                    : 'Confirmar Arribo'}
                            </Button>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
};

export default ModalConfirmarLlegadaReserva;
