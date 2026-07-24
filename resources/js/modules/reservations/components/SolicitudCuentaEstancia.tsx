import { WalletCards } from 'lucide-react';
import { Checkbox } from '@/modules/shared/ui/casilla';

interface PropiedadesSolicitudCuentaEstancia {
    solicitada: boolean;
    limite: number | null;
    alCambiarSolicitud: (solicitada: boolean) => void;
    alCambiarLimite: (limite: number | null) => void;
}

export const SolicitudCuentaEstancia = ({
    solicitada,
    limite,
    alCambiarSolicitud,
    alCambiarLimite,
}: PropiedadesSolicitudCuentaEstancia) => {
    return (
        <section className="rounded-3xl border border-border bg-background p-5">
            <div className="flex items-start gap-4">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                    <WalletCards className="h-5 w-5" />
                </div>
                <div className="flex-1 space-y-4">
                    <div className="flex items-start gap-3">
                        <Checkbox
                            id="solicita-cuenta-estancia"
                            checked={solicitada}
                            onCheckedChange={(valor) =>
                                alCambiarSolicitud(valor === true)
                            }
                        />
                        <label
                            htmlFor="solicita-cuenta-estancia"
                            className="cursor-pointer"
                        >
                            <span className="block text-sm font-black text-foreground">
                                Solicitar una cuenta de consumo
                            </span>
                            <span className="mt-1 block text-xs leading-relaxed text-muted-foreground">
                                Podrá cargar restaurante y servicios durante la
                                estancia. Recepción validará y abrirá la cuenta
                                al realizar el check-in.
                            </span>
                        </label>
                    </div>
                    {solicitada && (
                        <div>
                            <label
                                htmlFor="limite-cuenta-estancia"
                                className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase"
                            >
                                Límite estimado de consumo
                            </label>
                            <input
                                id="limite-cuenta-estancia"
                                type="number"
                                min={0}
                                step="0.01"
                                value={limite ?? ''}
                                onChange={(evento) =>
                                    alCambiarLimite(
                                        evento.target.value === ''
                                            ? null
                                            : Number(evento.target.value),
                                    )
                                }
                                className="w-full rounded-2xl border border-border bg-card px-4 py-3 text-sm font-semibold text-foreground transition outline-none focus:ring-2 focus:ring-bugambilia-500"
                                placeholder="Ej. 2500.00"
                            />
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
};
