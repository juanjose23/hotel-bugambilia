import { router } from '@inertiajs/react';
import { useMemo, useState, useTransition } from 'react';
import type { PromocionItem } from '../types';

interface UseFiltrosPromocionesProps {
    promociones: PromocionItem[];
    selectedCategory?: string | null;
    searchQuery?: string;
}

export const useFiltrosPromociones = ({
    promociones,
    selectedCategory = null,
    searchQuery = '',
}: UseFiltrosPromocionesProps) => {
    const [categoria, setCategoria] = useState<string>(
        selectedCategory || 'todas',
    );
    const [buscar, setBuscar] = useState<string>(searchQuery || '');
    const [isPending, startTransition] = useTransition();

    const manejarCambioCategoria = (nuevaCat: string) => {
        startTransition(() => {
            setCategoria(nuevaCat);
            router.get(
                '/promociones',
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

    const manejarSubmitBusqueda = () => {
        startTransition(() => {
            router.get(
                '/promociones',
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
                '/promociones',
                {},
                {
                    preserveState: true,
                    replace: true,
                },
            );
        });
    };

    const promocionesFiltradas = useMemo(() => {
        let resultado = [...promociones];

        if (categoria && categoria !== 'todas') {
            resultado = resultado.filter((p) =>
                p.tipo.toLowerCase().includes(categoria.toLowerCase()),
            );
        }

        if (buscar.trim() !== '') {
            const q = buscar.toLowerCase();

            resultado = resultado.filter(
                (p) =>
                    p.nombre.toLowerCase().includes(q) ||
                    p.descripcion?.toLowerCase().includes(q) ||
                    p.tipo.toLowerCase().includes(q) ||
                    p.codigo.toLowerCase().includes(q),
            );
        }

        return resultado;
    }, [promociones, categoria, buscar]);

    return {
        categoria,
        buscar,
        isPending,
        setBuscar,
        promocionesFiltradas,
        manejarCambioCategoria,
        manejarSubmitBusqueda,
        manejarReset,
    };
};

export default useFiltrosPromociones;
