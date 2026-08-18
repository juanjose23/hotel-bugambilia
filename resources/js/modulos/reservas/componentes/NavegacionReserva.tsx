import { ArrowLeft, ArrowRight, CheckCircle2 } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';

interface PropiedadesNavegacionReserva {
    pasoActual: number;
    totalPasos: number;
    procesando: boolean;
    alRetroceder: () => void;
}

export const NavegacionReserva = ({
    pasoActual,
    totalPasos,
    procesando,
    alRetroceder,
}: PropiedadesNavegacionReserva) => {
    const esUltimoPaso = pasoActual === totalPasos;

    return (
        <div className="flex items-center justify-between gap-4 pt-4 border-t border-border/60">
            {pasoActual > 1 ? (
                <Button
                    type="button"
                    onClick={(e) => {
                        e.preventDefault();
                        alRetroceder();
                    }}
                    variant="outline"
                    className="cursor-pointer rounded-full border-border/80 bg-background px-5 py-2.5 text-xs font-extrabold text-foreground transition-all hover:bg-muted"
                >
                    <ArrowLeft className="mr-1.5 size-4" />
                    Paso Anterior
                </Button>
            ) : (
                <div />
            )}

            <Button
                type="submit"
                disabled={procesando}
                className={`ml-auto cursor-pointer rounded-full bg-bugambilia-600 px-7 font-black text-white shadow-md hover:bg-bugambilia-700 transition-all ${
                    esUltimoPaso ? 'py-3.5 text-xs sm:text-sm bg-gradient-to-r from-bugambilia-700 to-bugambilia-600 shadow-lg' : 'py-2.5 text-xs'
                }`}
            >
                {esUltimoPaso ? (
                    <>
                        <CheckCircle2 className="mr-2 size-4.5" />
                        {procesando
                            ? 'Procesando reserva...'
                            : 'Confirmar Reserva Garantizada'}
                    </>
                ) : (
                    <>
                        Continuar al paso {pasoActual + 1}
                        <ArrowRight className="ml-1.5 size-4" />
                    </>
                )}
            </Button>
        </div>
    );
};

