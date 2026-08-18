import React from 'react';
import { X, ShieldCheck, CreditCard, RefreshCw, AlertCircle, Lock } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesModalCondiciones {
    estaAbierto: boolean;
    alCerrar: () => void;
    tipoPago?: string;
}

export const ModalCondicionesPagoCancelacion = ({
    estaAbierto,
    alCerrar,
    tipoPago = 'abono_50',
}: PropiedadesModalCondiciones) => {
    if (!estaAbierto) return null;

    return (
        <div className="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs animate-in fade-in duration-200">
            <div className="relative w-full max-w-lg overflow-hidden rounded-3xl border border-border/80 bg-card p-6 shadow-2xl space-y-5 font-sans animate-in zoom-in-95 duration-200">
                {/* Botón de Cierre Superior */}
                <button
                    type="button"
                    onClick={alCerrar}
                    className="absolute top-4 right-4 flex size-9 items-center justify-center rounded-full border border-border/80 bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    title="Cerrar modal"
                >
                    <X className="size-4.5" />
                </button>

                {/* Título Estilo Barceló Resort */}
                <div className="space-y-1 pr-8">
                    <Badge className="bg-bugambilia-500/10 text-[10px] font-black text-bugambilia-600 dark:text-bugambilia-400">
                        Políticas de Transparencia Hotel Bugambilias
                    </Badge>
                    <h3 className="text-xl font-black text-foreground tracking-tight">
                        Condiciones de pago y cancelación
                    </h3>
                </div>

                <div className="space-y-4 text-xs divide-y divide-border/60">
                    {/* Momento de Pago */}
                    <div className="pt-2 space-y-1.5">
                        <div className="flex items-center gap-2 font-black text-foreground text-sm">
                            <CreditCard className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                            <span>Momento de pago: Garantía del 50%</span>
                        </div>
                        <p className="text-muted-foreground leading-relaxed pl-6">
                            Para asegurar su habitación se requiere un pago de garantía del 50%. El 50% restante se abonará directamente al momento de realizar su Check-In en la recepción del hotel.
                        </p>
                    </div>

                    {/* Política de Cancelación */}
                    <div className="pt-3 space-y-1.5">
                        <div className="flex items-center gap-2 font-black text-foreground text-sm">
                            <RefreshCw className="size-4 text-emerald-600 dark:text-emerald-400" />
                            <span>Política de cancelación: Cancelación Flexible</span>
                        </div>
                        <p className="text-muted-foreground leading-relaxed pl-6">
                            Puede cancelar su reservación sin penalización hasta 48 horas antes de la fecha de entrada (14:00 hrs). En cancelaciones con menos de 48 horas o No Show, el importe abonado de garantía no será reembolsable.
                        </p>
                    </div>

                    {/* Garantía de Seguridad */}
                    <div className="pt-3 space-y-1.5">
                        <div className="flex items-center gap-2 font-black text-foreground text-sm">
                            <Lock className="size-4 text-amber-600 dark:text-amber-400" />
                            <span>Seguridad y Encriptación En Línea</span>
                        </div>
                        <p className="text-muted-foreground leading-relaxed pl-6">
                            Todos los pagos con tarjeta de crédito o débito son procesados con estándares de encriptación bancaria SSL de 256 bits a través de la pasarela certificada Stripe.
                        </p>
                    </div>
                </div>

                {/* Pie con Botón de Entendido */}
                <div className="pt-2 flex justify-end border-t border-border/60">
                    <Button
                        type="button"
                        onClick={alCerrar}
                        className="rounded-full bg-bugambilia-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-md hover:bg-bugambilia-700"
                    >
                        <ShieldCheck className="mr-1.5 size-4" /> Entendido y Acepto
                    </Button>
                </div>
            </div>
        </div>
    );
};
