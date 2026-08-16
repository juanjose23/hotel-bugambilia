import { router } from '@inertiajs/react';
import { useState } from 'react';
import type { SyntheticEvent } from 'react';
import type { EspacioItem } from '../interfaces/espacioInterfaces';

export const useFiltroEspacios = (
    espacios: EspacioItem[] = [],
    tipoInicial: string | null = null,
) => {
    const [activeTipo, setActiveTipo] = useState<string | null>(tipoInicial);
    const [term, setTerm] = useState<string>('');
    const [modalGaleria, setModalGaleria] = useState<{
        open: boolean;
        espacio?: EspacioItem;
    }>({ open: false });
    const [imgIndex, setImgIndex] = useState<number>(0);

    const handleSearchSubmit = (e?: SyntheticEvent) => {
        if (e && e.preventDefault) {
            e.preventDefault();
        }

        const params: Record<string, string> = {};

        if (term) {
            params.buscar = term;
        }

        if (activeTipo && activeTipo !== 'TODOS') {
            params.tipo = activeTipo;
        }

        router.get('/espacios', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const handleFilterTipo = (tipo: string | null) => {
        setActiveTipo(tipo);
        const params: Record<string, string> = {};

        if (term) {
            params.buscar = term;
        }

        if (tipo && tipo !== 'TODOS') {
            params.tipo = tipo;
        }

        router.get('/espacios', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const handleReset = () => {
        setTerm('');
        setActiveTipo(null);
        router.get('/espacios', {}, { preserveScroll: true, replace: true });
    };

    const espaciosFiltrados = espacios.filter((e) => {
        if (!term.trim()) {
            return true;
        }

        const q = term.toLowerCase();

        return (
            e.nombre.toLowerCase().includes(q) ||
            e.descripcion?.toLowerCase().includes(q) ||
            e.ubicacion?.toLowerCase().includes(q)
        );
    });

    const abrirGaleria = (espacio: EspacioItem) => {
        setModalGaleria({ open: true, espacio });
        setImgIndex(0);
    };

    const cerrarGaleria = () => {
        setModalGaleria({ open: false });
        setImgIndex(0);
    };

    return {
        activeTipo,
        term,
        setTerm,
        modalGaleria,
        imgIndex,
        setImgIndex,
        espaciosFiltrados,
        handleSearchSubmit,
        handleFilterTipo,
        handleReset,
        abrirGaleria,
        cerrarGaleria,
    };
};
