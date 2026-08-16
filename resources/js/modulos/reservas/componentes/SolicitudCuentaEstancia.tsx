import { WalletCards } from 'lucide-react';
import React from 'react';
import { Campo, EtiquetaCampo } from '@/modulos/compartido/ui/campo';
import { Checkbox } from '@/modulos/compartido/ui/casilla';
import { Input } from '@/modulos/compartido/ui/entrada';

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
        <section className="rounded-3xl border border-border bg-card p-5">
            <div className="flex items-start gap-4">
                <div className="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <WalletCards className="size-5" />
                </div>
                <div className="flex flex-1 flex-col gap-4">
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
                            className="flex cursor-pointer flex-col gap-1"
                        >
                            <span className="block text-sm font-black text-foreground">
                                Solicitar una cuenta de consumo
                            </span>
                            <span className="text-xs leading-relaxed text-muted-foreground">
                                Podrá cargar restaurante y servicios durante la
                                estancia. Recepción validará y abrirá la cuenta
                                al realizar el check-in.
                            </span>
                        </label>
                    </div>
                    {solicitada && (
                        <Campo>
                            <EtiquetaCampo htmlFor="limite-cuenta-estancia">
                                Límite estimado de consumo
                            </EtiquetaCampo>
                            <Input
                                id="limite-cuenta-estancia"
                                type="number"
                                min={0}
                                step="0.01"
                                value={limite ?? ''}
                                onChange={(
                                    evento: React.ChangeEvent<HTMLInputElement>,
                                ) =>
                                    alCambiarLimite(
                                        evento.target.value === ''
                                            ? null
                                            : Number(evento.target.value),
                                    )
                                }
                                placeholder="Ej. 2500.00"
                            />
                        </Campo>
                    )}
                </div>
            </div>
        </section>
    );
};
