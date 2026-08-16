import { Plus, Trash2 } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';

interface HuespedData {
    nombre: string;
    identificacion: string;
    esTitular: boolean;
}

interface PropiedadesPasoAcompanantesAutoCheckIn {
    huespedes: HuespedData[];
    onAgregar: () => void;
    onEliminar: (idx: number) => void;
    onActualizar: (
        idx: number,
        field: 'nombre' | 'identificacion',
        val: string,
    ) => void;
}

export const PasoAcompanantesAutoCheckIn = ({
    huespedes = [],
    onAgregar,
    onEliminar,
    onActualizar,
}: PropiedadesPasoAcompanantesAutoCheckIn) => {
    return (
        <div className="flex flex-col gap-5 font-sans">
            <div className="flex items-center justify-between border-b border-border/40 pb-3">
                <div>
                    <h3 className="text-lg font-black text-foreground">
                        Registro de Acompañantes
                    </h3>
                    <p className="text-xs text-muted-foreground">
                        Registre los nombres de las personas que le acompañan en
                        la habitación.
                    </p>
                </div>
                <Button
                    type="button"
                    onClick={onAgregar}
                    size="sm"
                    className="rounded-full bg-amber-500 text-xs font-extrabold text-black hover:bg-amber-600"
                >
                    <Plus className="mr-1 size-3.5" /> Agregar
                </Button>
            </div>

            <div className="flex flex-col gap-3">
                {huespedes.map((huesped, idx) => (
                    <div
                        key={idx}
                        className="flex flex-col gap-3 rounded-2xl border border-border/80 bg-background p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div className="grid flex-grow grid-cols-1 gap-3 sm:grid-cols-2">
                            <Input
                                type="text"
                                placeholder="Nombre completo"
                                value={huesped.nombre}
                                onChange={(e) =>
                                    onActualizar(idx, 'nombre', e.target.value)
                                }
                                className="rounded-xl text-xs font-semibold"
                            />
                            <Input
                                type="text"
                                placeholder="Identificación / Cédula"
                                value={huesped.identificacion}
                                onChange={(e) =>
                                    onActualizar(
                                        idx,
                                        'identificacion',
                                        e.target.value,
                                    )
                                }
                                className="rounded-xl text-xs font-semibold"
                            />
                        </div>
                        {!huesped.esTitular && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => onEliminar(idx)}
                                className="shrink-0 rounded-xl text-rose-500 hover:bg-rose-500/10"
                            >
                                <Trash2 className="size-4" />
                            </Button>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default PasoAcompanantesAutoCheckIn;
