import { Checkbox } from '@/modulos/compartido/ui/casilla';

interface PropiedadesPasoTerminosAutoCheckIn {
    aceptaPoliticas: boolean;
    onToggleAceptaPoliticas: (checked: boolean) => void;
}

export const PasoTerminosAutoCheckIn = ({
    aceptaPoliticas,
    onToggleAceptaPoliticas,
}: PropiedadesPasoTerminosAutoCheckIn) => {
    return (
        <div className="flex flex-col gap-5 font-sans">
            <div className="border-b border-border/40 pb-3">
                <h3 className="text-lg font-black text-foreground">
                    Aceptación de Políticas & Registro
                </h3>
                <p className="text-xs text-muted-foreground">
                    Lea y acepte el reglamento de hospitalidad del hotel.
                </p>
            </div>

            <div className="max-h-40 space-y-2 overflow-y-auto rounded-2xl border border-border/80 bg-muted/30 p-4 text-xs text-muted-foreground">
                <p className="font-bold text-foreground">
                    Políticas Principales:
                </p>
                <p>
                    • El Check-in formal es a partir de las 14:00 hrs. Check-out
                    a las 12:00 hrs.
                </p>
                <p>
                    • Se requiere mantener el orden y la tranquilidad en áreas
                    de habitaciones a partir de las 22:00 hrs.
                </p>
                <p>
                    • El hotel no se hace responsable por objetos de valor no
                    resguardados en caja fuerte.
                </p>
            </div>

            <div className="flex items-center space-x-3 pt-2">
                <Checkbox
                    id="aceptaPoliticas"
                    checked={aceptaPoliticas}
                    onCheckedChange={(checked) =>
                        onToggleAceptaPoliticas(Boolean(checked))
                    }
                />
                <label
                    htmlFor="aceptaPoliticas"
                    className="cursor-pointer text-xs font-bold text-foreground"
                >
                    Acepto las políticas del hotel y confirmo que los datos
                    ingresados son verídicos.
                </label>
            </div>
        </div>
    );
};

export default PasoTerminosAutoCheckIn;
