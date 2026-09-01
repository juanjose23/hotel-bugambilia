import { useState, useMemo } from 'react';
import type {
    FiltrosActivos,
    FiltrosDisponiblesDomain,
    RoomItem,
} from '@/modules/shared/types';

interface OpcionesFiltrosHabitaciones {
    habitaciones?: RoomItem[];
    filtrosDisponibles?: FiltrosDisponiblesDomain;
}

export const useFiltrosHabitaciones = ({
    habitaciones = [],
    filtrosDisponibles = {
        categorias: [],
        servicios: [],
        capacidades: [1, 2, 3, 4],
        precioMin: 35,
        precioMax: 150,
    },
}: OpcionesFiltrosHabitaciones) => {
    const minP = filtrosDisponibles.precioMin || 35;
    const maxP = filtrosDisponibles.precioMax || 150;

    const [sheetFiltrosAbierto, setSheetFiltrosAbierto] = useState(false);
    const [filtros, setFiltros] = useState<FiltrosActivos>({
        categoria: 'todas',
        precioMin: minP,
        precioMax: maxP,
        capacidad: 0,
        serviciosIds: [],
    });

    // Conteo de filtros activos
    const totalFiltrosActivos = useMemo(() => {
        let count = 0;

        if (filtros.categoria !== 'todas') {
            count++;
        }

        if (filtros.precioMin > minP || filtros.precioMax < maxP) {
            count++;
        }

        if (filtros.capacidad > 0) {
            count++;
        }

        if (filtros.serviciosIds.length > 0) {
            count += filtros.serviciosIds.length;
        }

        return count;
    }, [filtros, minP, maxP]);

    // Filtrado reactivo en memoria con los datos de la BD
    const habitacionesFiltradas = useMemo(() => {
        return habitaciones.filter((h) => {
            if (
                filtros.categoria !== 'todas' &&
                !h.categoria
                    ?.toLowerCase()
                    .includes(filtros.categoria.toLowerCase())
            ) {
                return false;
            }

            const precio = Number(h.precio);

            if (precio < filtros.precioMin || precio > filtros.precioMax) {
                return false;
            }

            if (
                filtros.capacidad > 0 &&
                (h.capacidad || 2) < filtros.capacidad
            ) {
                return false;
            }

            // Filtro por Servicios de habitacion
            if (filtros.serviciosIds.length > 0) {
                const ids = (h.servicios_ids || []).map(Number);
                const tieneServicio = filtros.serviciosIds.some((sId) =>
                    ids.includes(Number(sId)),
                );

                if (!tieneServicio) {
                    return false;
                }
            }

            return true;
        });
    }, [habitaciones, filtros]);

    const manejarReset = () => {
        setFiltros({
            categoria: 'todas',
            precioMin: minP,
            precioMax: maxP,
            capacidad: 0,
            serviciosIds: [],
        });
    };

    return {
        filtros,
        setFiltros,
        sheetFiltrosAbierto,
        setSheetFiltrosAbierto,
        totalFiltrosActivos,
        habitacionesFiltradas,
        manejarReset,
        minP,
        maxP,
    };
};

export default useFiltrosHabitaciones;
