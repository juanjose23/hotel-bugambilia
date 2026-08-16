import { router } from '@inertiajs/react';
import { useState } from 'react';
import type { SyntheticEvent } from 'react';

export const useFiltroServicios = (
    searchQuery: string = '',
    selectedCategory: string | null = null,
) => {
    const [term, setTerm] = useState(searchQuery);

    const handleSearchSubmit = (e: SyntheticEvent) => {
        e.preventDefault();
        const params: Record<string, string> = {};

        if (term) {
            params.buscar = term;
        }

        if (selectedCategory) {
            params.categoria = selectedCategory;
        }

        router.get('/servicios', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const handleCategorySelect = (categoria: string | null) => {
        const params: Record<string, string> = {};

        if (term) {
            params.buscar = term;
        }

        if (categoria) {
            params.categoria = categoria;
        }

        router.get('/servicios', params, {
            preserveScroll: true,
            replace: true,
        });
    };

    const handleReset = () => {
        setTerm('');
        router.get('/servicios', {}, { preserveScroll: true, replace: true });
    };

    return {
        term,
        setTerm,
        handleSearchSubmit,
        handleCategorySelect,
        handleReset,
    };
};
