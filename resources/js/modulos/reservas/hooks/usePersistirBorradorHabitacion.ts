import { useEffect } from 'react';
import type { DatosBorradorHabitacion } from '@/modulos/reservas/interfaces/borradorReserva';

interface OpcionesPersistirBorradorHabitacion {
    slug: string;
    pasoActual: number;
    data: DatosBorradorHabitacion;
    guardarBorrador: (borrador: {
        tipo: 'habitacion';
        rutaRetorno: string;
        pasoActual: number;
        datos: DatosBorradorHabitacion;
    }) => void;
}

export function usePersistirBorradorHabitacion({
    slug,
    pasoActual,
    data,
    guardarBorrador,
}: OpcionesPersistirBorradorHabitacion) {
    useEffect(() => {
        guardarBorrador({
            tipo: 'habitacion',
            rutaRetorno: `/habitaciones/${slug}/reservar`,
            pasoActual,
            datos: data,
        });
    }, [data, guardarBorrador, pasoActual, slug]);
}
