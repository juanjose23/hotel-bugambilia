import { router } from '@inertiajs/react';
import { useMemo, useState, useTransition } from 'react';
import type { RoomItem } from '@/modules/shared/types';

interface UseFiltrosHabitacionesPageProps {
    rooms: RoomItem[];
    selectedCategory?: string | null;
    searchQuery?: string;
}

export const useFiltrosHabitacionesPage = ({
    rooms,
    selectedCategory = null,
    searchQuery = '',
}: UseFiltrosHabitacionesPageProps) => {
    const [categoria, setCategoria] = useState<string>(
        selectedCategory || 'todas',
    );
    const [buscar, setBuscar] = useState<string>(searchQuery || '');
    const [huespedes, setHuespedes] = useState<string>('todos');
    const [orden, setOrden] = useState<
        'precio_asc' | 'precio_desc' | 'popular'
    >('popular');
    const [isPending, startTransition] = useTransition();

    const manejarCambioCategoria = (nuevaCat: string) => {
        startTransition(() => {
            setCategoria(nuevaCat);
            router.get(
                '/habitaciones',
                {
                    categoria: nuevaCat === 'todas' ? undefined : nuevaCat,
                    buscar: buscar || undefined,
                    huespedes: huespedes === 'todos' ? undefined : huespedes,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    const manejarSubmitBusqueda = () => {
        startTransition(() => {
            router.get(
                '/habitaciones',
                {
                    categoria: categoria === 'todas' ? undefined : categoria,
                    buscar: buscar || undefined,
                    huespedes: huespedes === 'todos' ? undefined : huespedes,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    const manejarReset = () => {
        startTransition(() => {
            setCategoria('todas');
            setBuscar('');
            setHuespedes('todos');
            setOrden('popular');
            router.get(
                '/habitaciones',
                {},
                {
                    preserveState: true,
                    replace: true,
                },
            );
        });
    };

    const habitacionesFiltradas = useMemo(() => {
        let resultado = [...rooms];

        if (categoria && categoria !== 'todas') {
            resultado = resultado.filter((h) =>
                h.categoria?.toLowerCase().includes(categoria.toLowerCase()),
            );
        }

        if (buscar.trim() !== '') {
            const query = buscar.toLowerCase();

            resultado = resultado.filter(
                (h) =>
                    h.nombre.toLowerCase().includes(query) ||
                    h.descripcion?.toLowerCase().includes(query) ||
                    h.categoria?.toLowerCase().includes(query),
            );
        }

        if (huespedes && huespedes !== 'todos') {
            const numHuespedes = Number(huespedes);

            resultado = resultado.filter(
                (h) => (h.capacidad || 2) >= numHuespedes,
            );
        }

        if (orden === 'precio_asc') {
            resultado.sort(
                (a, b) => Number(a.precio || 0) - Number(b.precio || 0),
            );
        } else if (orden === 'precio_desc') {
            resultado.sort(
                (a, b) => Number(b.precio || 0) - Number(a.precio || 0),
            );
        }

        return resultado;
    }, [rooms, categoria, buscar, huespedes, orden]);

    return {
        categoria,
        buscar,
        huespedes,
        orden,
        isPending,
        habitacionesFiltradas,
        setBuscar,
        setHuespedes,
        setOrden,
        manejarCambioCategoria,
        manejarSubmitBusqueda,
        manejarReset,
    };
};

export default useFiltrosHabitacionesPage;
