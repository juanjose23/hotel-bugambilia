import { router } from '@inertiajs/react';
import { useMemo, useState, useTransition } from 'react';
import type { RoomItem } from '@/modules/shared/types';

interface UseFiltrosHabitacionesPageProps {
    rooms: RoomItem[];
    selectedCategory?: string | null;
    searchQuery?: string;
    initialCheckIn?: string;
    initialCheckOut?: string;
    initialHuespedes?: string;
}

export const useFiltrosHabitacionesPage = ({
    rooms,
    selectedCategory = null,
    searchQuery = '',
    initialCheckIn = '',
    initialCheckOut = '',
    initialHuespedes = 'todos',
}: UseFiltrosHabitacionesPageProps) => {
    // Leer parámetros de URL si no vienen por props
    const urlParams =
        typeof window !== 'undefined'
            ? new URLSearchParams(window.location.search)
            : null;
    const defaultCheckIn = initialCheckIn || urlParams?.get('check_in') || '';
    const defaultCheckOut =
        initialCheckOut || urlParams?.get('check_out') || '';
    const defaultHuespedes =
        initialHuespedes !== 'todos'
            ? initialHuespedes
            : urlParams?.get('huespedes') || 'todos';

    const [categoria, setCategoria] = useState<string>(
        selectedCategory || 'todas',
    );
    const [buscar, setBuscar] = useState<string>(searchQuery || '');
    const [huespedes, setHuespedes] = useState<string>(defaultHuespedes);
    const [checkIn, setCheckIn] = useState<string>(defaultCheckIn);
    const [checkOut, setCheckOut] = useState<string>(defaultCheckOut);
    const [orden, setOrden] = useState<
        'precio_asc' | 'precio_desc' | 'popular'
    >('popular');
    const [isPending, startTransition] = useTransition();

    // Cálculo reactivo de noches
    const noches = useMemo(() => {
        if (!checkIn || !checkOut) {
            return 0;
        }

        try {
            const fechaIn = new Date(`${checkIn}T12:00:00`);
            const fechaOut = new Date(`${checkOut}T12:00:00`);
            const diffTime = fechaOut.getTime() - fechaIn.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            return diffDays > 0 ? diffDays : 0;
        } catch {
            return 0;
        }
    }, [checkIn, checkOut]);

    const manejarCambioCategoria = (nuevaCat: string) => {
        startTransition(() => {
            setCategoria(nuevaCat);
            router.get(
                '/habitaciones',
                {
                    categoria: nuevaCat === 'todas' ? undefined : nuevaCat,
                    buscar: buscar || undefined,
                    huespedes: huespedes === 'todos' ? undefined : huespedes,
                    check_in: checkIn || undefined,
                    check_out: checkOut || undefined,
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
                    check_in: checkIn || undefined,
                    check_out: checkOut || undefined,
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
            setCheckIn('');
            setCheckOut('');
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
        checkIn,
        checkOut,
        noches,
        orden,
        isPending,
        habitacionesFiltradas,
        setBuscar,
        setHuespedes,
        setCheckIn,
        setCheckOut,
        setOrden,
        manejarCambioCategoria,
        manejarSubmitBusqueda,
        manejarReset,
    };
};

export default useFiltrosHabitacionesPage;
