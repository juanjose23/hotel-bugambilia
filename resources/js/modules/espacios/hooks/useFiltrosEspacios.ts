import { router } from '@inertiajs/react';
import { useMemo, useState, useTransition } from 'react';
import type { FiltrosEspaciosState, EspacioItem } from '../types';

interface UseFiltrosEspaciosProps {
    espacios: EspacioItem[];
    tipoSeleccionado?: string;
}

export const useFiltrosEspacios = ({
    espacios,
    tipoSeleccionado = 'TODOS',
}: UseFiltrosEspaciosProps) => {
    const [filtros, setFiltros] = useState<FiltrosEspaciosState>({
        tipo: tipoSeleccionado || 'TODOS',
        capacidadMinima: 1,
        buscar: '',
    });

    const [isPending, startTransition] = useTransition();

    const manejarCambioTipo = (tipo: string) => {
        startTransition(() => {
            setFiltros((prev) => ({ ...prev, tipo }));
            router.get(
                '/espacios',
                {
                    tipo: tipo === 'TODOS' ? undefined : tipo,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    const manejarCambioBusqueda = (buscar: string) => {
        setFiltros((prev) => ({ ...prev, buscar }));
    };

    const manejarCambioCapacidad = (capacidadMinima: number) => {
        setFiltros((prev) => ({ ...prev, capacidadMinima }));
    };

    const manejarReset = () => {
        startTransition(() => {
            setFiltros({
                tipo: 'TODOS',
                capacidadMinima: 1,
                buscar: '',
            });
            router.get(
                '/espacios',
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    // Filtrado reactivo en el cliente
    const espaciosFiltrados = useMemo(() => {
        return espacios.filter((espacio) => {
            const coincideTipo =
                filtros.tipo.toUpperCase() === 'TODOS' ||
                espacio.tipo.toLowerCase() === filtros.tipo.toLowerCase();

            const coincideCapacidad =
                (espacio.capacidad ?? 0) >= filtros.capacidadMinima;

            const terminoBusqueda = filtros.buscar.toLowerCase().trim();
            const coincideBusqueda =
                !terminoBusqueda ||
                espacio.nombre.toLowerCase().includes(terminoBusqueda) ||
                (espacio.descripcion?.toLowerCase().includes(terminoBusqueda) ??
                    false) ||
                (espacio.ubicacion?.toLowerCase().includes(terminoBusqueda) ??
                    false);

            return coincideTipo && coincideCapacidad && coincideBusqueda;
        });
    }, [espacios, filtros.tipo, filtros.capacidadMinima, filtros.buscar]);

    return {
        filtros,
        espaciosFiltrados,
        isPending,
        manejarCambioTipo,
        manejarCambioBusqueda,
        manejarCambioCapacidad,
        manejarReset,
    };
};
