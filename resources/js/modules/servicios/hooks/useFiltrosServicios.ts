import { router } from '@inertiajs/react';
import { useMemo, useState, useTransition } from 'react';
import type { ServicioItem } from '../types';

interface UseFiltrosServiciosProps {
    services: ServicioItem[];
    selectedCategory?: string | null;
    searchQuery?: string;
}

export const useFiltrosServicios = ({
    services,
    selectedCategory = null,
    searchQuery = '',
}: UseFiltrosServiciosProps) => {
    const [categoria, setCategoria] = useState<string>(
        selectedCategory || 'todas',
    );
    const [buscar, setBuscar] = useState<string>(searchQuery || '');
    const [isPending, startTransition] = useTransition();

    const manejarCambioCategoria = (nuevaCat: string) => {
        startTransition(() => {
            setCategoria(nuevaCat);
            router.get(
                '/servicios',
                {
                    categoria: nuevaCat === 'todas' ? undefined : nuevaCat,
                    buscar: buscar || undefined,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    const manejarCambioBusqueda = (nuevaBusqueda: string) => {
        setBuscar(nuevaBusqueda);
    };

    const manejarSubmitBusqueda = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        startTransition(() => {
            router.get(
                '/servicios',
                {
                    categoria: categoria === 'todas' ? undefined : categoria,
                    buscar: buscar || undefined,
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
            router.get(
                '/servicios',
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        });
    };

    // Filtrado del cliente para respuesta inmediata
    const serviciosFiltrados = useMemo(() => {
        return services.filter((servicio) => {
            const coincideCategoria =
                categoria === 'todas' ||
                servicio.categoria
                    ?.toLowerCase()
                    .includes(categoria.toLowerCase());

            const terminoBusqueda = buscar.toLowerCase().trim();
            const coincideBusqueda =
                !terminoBusqueda ||
                servicio.nombre.toLowerCase().includes(terminoBusqueda) ||
                (servicio.descripcion
                    ?.toLowerCase()
                    .includes(terminoBusqueda) ??
                    false) ||
                (servicio.categoria?.toLowerCase().includes(terminoBusqueda) ??
                    false);

            return coincideCategoria && coincideBusqueda;
        });
    }, [services, categoria, buscar]);

    return {
        filtros: { categoria, buscar },
        serviciosFiltrados,
        isPending,
        manejarCambioCategoria,
        manejarCambioBusqueda,
        manejarSubmitBusqueda,
        manejarReset,
    };
};
