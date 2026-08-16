import { Checkbox } from '@/modulos/compartido/ui/casilla';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesPasoCuentaCreditoAutoCheckIn {
    solicitaCuenta: boolean;
    limiteCuenta: number;
    onToggleSolicitaCuenta: (checked: boolean) => void;
    onUpdateLimite: (val: number) => void;
}

export const PasoCuentaCreditoAutoCheckIn = ({
    solicitaCuenta,
    limiteCuenta,
    onToggleSolicitaCuenta,
    onUpdateLimite,
}: PropiedadesPasoCuentaCreditoAutoCheckIn) => {
    return (
        <div className="flex flex-col gap-5 font-sans">
            <div className="border-b border-border/40 pb-3">
                <h3 className="text-lg font-black text-foreground">
                    Cuenta Inicial & Autorizaciones
                </h3>
                <p className="text-xs text-muted-foreground">
                    Configure la línea de crédito para restaurante y servicios
                    de la estancia.
                </p>
            </div>

            <div className="space-y-4 rounded-2xl border border-border/80 bg-background p-5">
                <div className="flex items-center space-x-3">
                    <Checkbox
                        id="solicitaCuenta"
                        checked={solicitaCuenta}
                        onCheckedChange={(checked) =>
                            onToggleSolicitaCuenta(Boolean(checked))
                        }
                    />
                    <label
                        htmlFor="solicitaCuenta"
                        className="cursor-pointer text-xs font-bold text-foreground"
                    >
                        Habilitar consumo a la habitación (Restaurante, Bar &
                        Room Service)
                    </label>
                </div>

                {solicitaCuenta && (
                    <div className="flex flex-col gap-1.5 pt-2">
                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                            Límite de Crédito Autorizado ($ USD)
                        </label>
                        <Input
                            type="number"
                            value={limiteCuenta}
                            onChange={(e) =>
                                onUpdateLimite(Number(e.target.value))
                            }
                            className="max-w-xs rounded-2xl text-xs font-bold"
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

export default PasoCuentaCreditoAutoCheckIn;
