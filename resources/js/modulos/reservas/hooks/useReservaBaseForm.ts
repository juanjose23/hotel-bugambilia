import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export function useReservaBaseForm<TForm extends Record<string, any>>(
    initialData: TForm,
    maxPasos: number = 4,
) {
    const [pasoActual, setPasoActual] = useState(1);
    const form = useForm<TForm>(initialData);

    const avanzarPaso = () => {
        setPasoActual((prev) => Math.min(prev + 1, maxPasos));
    };

    const retrocederPaso = () => {
        setPasoActual((prev) => Math.max(prev - 1, 1));
    };

    const irAlPaso = (paso: number) => {
        if (paso >= 1 && paso <= maxPasos) {
            setPasoActual(paso);
        }
    };

    return {
        pasoActual,
        avanzarPaso,
        retrocederPaso,
        irAlPaso,
        form,
    };
}
