import { Link } from '@inertiajs/react';
import { CheckCircle2, MessageSquare, UtensilsCrossed } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { RespuestaReservaMesaBackend } from '../../services/restauranteService';

interface RestauranteReservaExitosaProps {
    reservaCreada: RespuestaReservaMesaBackend;
    nombreRestaurante: string;
    variante: 'pagina' | 'modal';
    alReiniciar?: () => void;
}

export const RestauranteReservaExitosa = ({
    reservaCreada,
    nombreRestaurante,
    variante,
    alReiniciar,
}: RestauranteReservaExitosaProps) => {
    const esModal = variante === 'modal';

    return (
        <div
            className={
                esModal
                    ? 'animate-in fade-in zoom-in-95 flex grow flex-col items-center justify-center py-8 text-center duration-300'
                    : 'animate-in fade-in zoom-in-95 mx-auto max-w-xl rounded-3xl border border-emerald-500/30 bg-card p-8 text-center shadow-lg duration-300'
            }
        >
            <div className="flex size-16 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 shadow-md dark:text-emerald-400">
                <CheckCircle2 className="size-9" />
            </div>

            <span className="mt-4 inline-block rounded-full bg-emerald-500/10 px-3.5 py-1 text-[11px] font-black tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                Reserva Confirmada
            </span>

            <h3
                className={`mt-2 font-black text-foreground ${esModal ? 'text-xl' : 'text-2xl'}`}
            >
                Código #{reservaCreada.codigo_reserva}
            </h3>

            <p className="mt-2 text-xs text-muted-foreground">
                {esModal
                    ? 'Tu mesa ha sido reservada con éxito. Puedes presentar este código o notificar al restaurante.'
                    : `Hemos reservado tu mesa en ${nombreRestaurante}. Te esperamos puntualmente.`}
            </p>

            <div className="mt-6 w-full space-y-2.5 rounded-2xl border border-border/80 bg-muted/30 p-4 text-left text-xs">
                <div className="flex justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Fecha:</span>
                    <span className="font-bold">{reservaCreada.fecha}</span>
                </div>
                <div className="flex justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Horario:</span>
                    <span className="font-bold">{reservaCreada.hora}</span>
                </div>
                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground">Mesa(s):</span>
                    <span className="rounded-lg border border-primary/20 bg-primary/10 px-2.5 py-0.5 font-black text-primary dark:text-rose-400">
                        {reservaCreada.mesas_unidas.join(' + ')}
                    </span>
                </div>
            </div>

            <div className="mt-6 flex w-full flex-col gap-3 sm:flex-row">
                {reservaCreada.whatsapp_url && (
                    <a
                        href={reservaCreada.whatsapp_url}
                        target="_blank"
                        rel="noreferrer"
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95"
                    >
                        <MessageSquare className="size-4" />
                        {esModal
                            ? 'Enviar Confirmación por WhatsApp'
                            : 'Notificar por WhatsApp'}
                    </a>
                )}

                {esModal ? (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alReiniciar}
                        className="h-11 w-full rounded-2xl text-xs font-bold"
                    >
                        Cerrar y volver al menú
                    </Button>
                ) : (
                    <Link
                        href="/restaurante"
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl border border-border bg-muted/60 py-3 text-xs font-bold text-foreground hover:bg-muted"
                    >
                        <UtensilsCrossed className="size-4" />
                        Volver al Menú
                    </Link>
                )}
            </div>
        </div>
    );
};

export default RestauranteReservaExitosa;
