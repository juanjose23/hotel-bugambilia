import { useState } from 'react';
interface OpcionesPasosReserva {
    totalPasos: number;
    pasoInicial?: number;
}
export const usePasosReserva = ({
    totalPasos,
    pasoInicial = 1,
}: OpcionesPasosReserva) => {
    const [pasoActual, establecerPasoActual] = useState(pasoInicial);
    const avanzar = () => {
        establecerPasoActual((paso) => Math.min(paso + 1, totalPasos));
    };
    const retroceder = () => {
        establecerPasoActual((paso) => Math.max(paso - 1, 1));
    };
    const irAlPaso = (paso: number) => {
        establecerPasoActual(Math.min(Math.max(paso, 1), totalPasos));
    };

    return {
        pasoActual,
        avanzar,
        retroceder,
        irAlPaso,
    };
};
