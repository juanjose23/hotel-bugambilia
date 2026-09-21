import { Link } from '@inertiajs/react';
import {
    CheckCircle2,
    Download,
    Copy,
    Check,
    ShieldCheck,
    MessageSquare,
    UtensilsCrossed,
    Hotel,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';

export interface DetalleItemReserva {
    label: string;
    valor: string;
}

export interface ReservaConfirmadaProps {
    codigoReserva: string;
    reservaId?: number | string;
    titulo?: string;
    subtitulo?: string;
    detalles?: DetalleItemReserva[];
    urlVoucher?: string;
    whatsappUrl?: string;
    urlRetorno?: string;
    textoRetorno?: string;
    variante?: 'pagina' | 'modal';
    alCerrar?: () => void;
}

export const ReservaConfirmada = ({
    codigoReserva,
    reservaId,
    titulo = '¡Reserva Confirmada con Éxito!',
    subtitulo = 'Hemos registrado tu solicitud en Hotel Bugambilias. Te esperamos puntualmente.',
    detalles = [],
    urlVoucher,
    whatsappUrl,
    urlRetorno = '/',
    textoRetorno = 'Volver al Inicio',
    variante = 'pagina',
    alCerrar,
}: ReservaConfirmadaProps) => {
    const [copiado, setCopiado] = useState(false);

    const copiarCodigo = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(codigoReserva);
            setCopiado(true);
            setTimeout(() => setCopiado(false), 2000);
        }
    };

    const voucherUrlFinal =
        urlVoucher ||
        (reservaId
            ? `/reservas/${reservaId}/voucher?codigo=${encodeURIComponent(codigoReserva)}`
            : undefined);

    const esModal = variante === 'modal';

    return (
        <div
            className={`text-center font-sans ${
                esModal
                    ? 'animate-in fade-in zoom-in-95 flex grow flex-col items-center justify-center p-4 duration-300'
                    : 'animate-in fade-in zoom-in-95 mx-auto max-w-xl space-y-6 rounded-3xl border border-border/80 bg-card p-6 shadow-xl duration-300 sm:p-8'
            }`}
        >
            {/* Ícono de Éxito */}
            <div className="mx-auto flex size-16 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 shadow-md dark:text-emerald-400">
                <CheckCircle2 className="size-9" />
            </div>

            <div>
                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                    <ShieldCheck className="size-3.5" />
                    <span>Confirmación Garantizada</span>
                </span>
                <h2
                    className={`mt-2 font-black tracking-tight text-foreground ${
                        esModal ? 'text-lg sm:text-xl' : 'text-xl sm:text-2xl'
                    }`}
                >
                    {titulo}
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                    {subtitulo}
                </p>
            </div>

            {/* Código de Confirmación con Copiar */}
            <div className="w-full rounded-2xl border border-primary/20 bg-primary/5 p-4 text-center dark:bg-rose-950/20">
                <div className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                    Código de Confirmación
                </div>
                <div className="mt-1 flex items-center justify-center gap-2">
                    <span className="font-mono text-2xl font-black text-primary sm:text-3xl dark:text-rose-400">
                        {codigoReserva}
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

            {/* Detalles de la Reserva si existen */}
            {detalles.length > 0 && (
                <div className="w-full space-y-2 rounded-2xl border border-border/80 bg-muted/25 p-4 text-left text-xs">
                    {detalles.map((d, idx) => (
                        <div
                            key={idx}
                            className={`flex justify-between ${
                                idx < detalles.length - 1
                                    ? 'border-b border-border/60 pb-2'
                                    : ''
                            }`}
                        >
                            <span className="text-muted-foreground">
                                {d.label}:
                            </span>
                            <span className="font-bold text-foreground">
                                {d.valor}
                            </span>
                        </div>
                    ))}
                </div>
            )}

            {/* Botones de Acción */}
            <div className="mt-4 flex w-full flex-col gap-3 sm:flex-row">
                {voucherUrlFinal && (
                    <a
                        href={voucherUrlFinal}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-foreground py-3 text-xs font-bold text-background shadow-md transition-all hover:bg-foreground/90 active:scale-95"
                    >
                        <Download className="size-4" />
                        <span>Descargar Voucher PDF</span>
                    </a>
                )}

                {whatsappUrl && (
                    <a
                        href={whatsappUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95"
                    >
                        <MessageSquare className="size-4" />
                        <span>Notificar por WhatsApp</span>
                    </a>
                )}

                {esModal && alCerrar ? (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alCerrar}
                        className="h-11 w-full rounded-2xl text-xs font-bold"
                    >
                        Cerrar
                    </Button>
                ) : (
                    <Link
                        href={urlRetorno}
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl border border-border bg-muted/60 py-3 text-xs font-bold text-foreground hover:bg-muted"
                    >
                        {urlRetorno.includes('restaurante') ? (
                            <UtensilsCrossed className="size-4" />
                        ) : (
                            <Hotel className="size-4" />
                        )}
                        <span>{textoRetorno}</span>
                    </Link>
                )}
            </div>
        </div>
    );
};

export default ReservaConfirmada;
