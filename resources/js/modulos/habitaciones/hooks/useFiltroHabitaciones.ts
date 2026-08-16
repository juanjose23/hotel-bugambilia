import { router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import type { SyntheticEvent } from 'react';
import type { HabitacionGrupo } from '../interfaces/habitacionInterfaces';

export const useFiltroHabitaciones = (
    habitacionesIniciales: HabitacionGrupo[] = [],
    categoriaInicial: string | null = null,
    busquedaInicial: string = '',
) => {
    const [categoriaSel, setCategoriaSel] = useState<string | null>(
        categoriaInicial,
    );
    const [busqueda, setBusqueda] = useState<string>(busquedaInicial);
    const [filtroCapacidad, setFiltroCapacidad] = useState<number | null>(null);
    const [precioMax, setPrecioMax] = useState<number | null>(null);

    const handleSearchSubmit = (e?: SyntheticEvent) => {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        const params: Record<string, string> = {};

        if (busqueda) {
            params.buscar = busqueda;
        }

        if (categoriaSel) {
            params.categoria = categoriaSel;
        }

        router.get('/habitaciones', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const handleSeleccionarCategoria = (cat: string | null) => {
        setCategoriaSel(cat);
        const params: Record<string, string> = {};

        if (busqueda) {
            params.buscar = busqueda;
        }

        if (cat) {
            params.categoria = cat;
        }

        router.get('/habitaciones', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const habitacionesFiltradas = useMemo(() => {
        return habitacionesIniciales.filter((room) => {
            // Filtro por término de búsqueda
            if (busqueda.trim()) {
                const q = busqueda.toLowerCase();
                const matchNombre =
                    room.nombre?.toLowerCase().includes(q) ||
                    room.name?.toLowerCase().includes(q);
                const matchDesc = room.descripcion?.toLowerCase().includes(q);
                const matchCat = room.categoria?.toLowerCase().includes(q);

                if (!matchNombre && !matchDesc && !matchCat) {
                    return false;
                }
            }

            // Filtro por capacidad de huéspedes
            if (filtroCapacidad !== null) {
                const cap =
                    typeof room.capacidad === 'number'
                        ? room.capacidad
                        : parseInt(String(room.capacidad)) || 2;

                if (cap < filtroCapacidad) {
                    return false;
                }
            }

            // Filtro por precio máximo
            if (precioMax !== null && precioMax > 0) {
                const rawPrice =
                    room.precio ??
                    room.precio_desde ??
                    room.precio_noche ??
                    room.precio_base ??
                    room.price ??
                    0;
                const p =
                    typeof rawPrice === 'string'
                        ? parseFloat(rawPrice) || 0
                        : Number(rawPrice) || 0;

                if (p > precioMax) {
                    return false;
                }
            }

            return true;
        });
    }, [habitacionesIniciales, busqueda, filtroCapacidad, precioMax]);

    const limpiarFiltros = () => {
        setBusqueda('');
        setCategoriaSel(null);
        setFiltroCapacidad(null);
        setPrecioMax(null);
        router.get(
            '/habitaciones',
            {},
            { preserveScroll: true, replace: true },
        );
    };

    return {
        categoriaSel,
        setCategoriaSel,
        busqueda,
        setBusqueda,
        filtroCapacidad,
        setFiltroCapacidad,
        precioMax,
        setPrecioMax,
        habitacionesFiltradas,
        handleSearchSubmit,
        handleSeleccionarCategoria,
        limpiarFiltros,
    };
};
