import { ArrowLeft, ArrowRight, CheckCircle2 } from 'lucide-react';
import { Button } from '@/modules/shared/ui/boton';
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
        <div className="flex items-center justify-between gap-4 pt-2">
            {pasoActual > 1 ? (
                <Button
                    type="button"
                    onClick={alRetroceder}
                    variant="outline"
                    className="cursor-pointer rounded-2xl border-border px-6 py-3.5 text-xs font-bold"
                >
                    <ArrowLeft className="mr-1.5 h-4 w-4" />
                    Paso anterior
                </Button>
            ) : (
                <div />
            )}

            <Button
                type="submit"
                disabled={procesando}
                className={`ml-auto cursor-pointer rounded-2xl bg-bugambilia-600 px-8 font-black text-white shadow-md hover:bg-bugambilia-700 ${esUltimoPaso ? 'py-4 text-sm shadow-lg' : 'py-3.5 text-xs'}`}
            >
                {esUltimoPaso ? (
                    <>
                        <CheckCircle2 className="mr-2 h-5 w-5" />
                        {procesando
                            ? 'Enviando solicitud...'
                            : 'Confirmar reserva garantizada'}
                    </>
                ) : (
                    <>
                        Continuar al paso {pasoActual + 1}
                        <ArrowRight className="ml-1.5 h-4 w-4" />
                    </>
                )}
            </Button>
        </div>
    );
};
