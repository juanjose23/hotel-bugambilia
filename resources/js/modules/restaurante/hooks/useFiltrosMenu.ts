import { useMemo, useState } from 'react';
import type { MenuItemData } from '../types';

interface UseFiltrosMenuProps {
    menu: MenuItemData[];
}

export const useFiltrosMenu = ({ menu = [] }: UseFiltrosMenuProps) => {
    const [categoriaActiva, setCategoriaActiva] = useState<string>('todos');
    const [busqueda, setBusqueda] = useState<string>('');

    const categoriasDisponibles = useMemo(() => {
        const setCats = new Set<string>();

        menu.forEach((item) => {
            if (item.categoria && item.categoria.trim() !== '') {
                setCats.add(item.categoria);
            }
        });

        return Array.from(setCats);
    }, [menu]);

    const menuFiltrado = useMemo(() => {
        return menu.filter((item) => {
            const coincideCategoria =
                categoriaActiva === 'todos' ||
                item.categoria === categoriaActiva;

            const coincideBusqueda =
                busqueda.trim() === '' ||
                item.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
                item.descripcion.toLowerCase().includes(busqueda.toLowerCase());

            return coincideCategoria && coincideBusqueda;
        });
    }, [menu, categoriaActiva, busqueda]);

    return {
        categoriaActiva,
        setCategoriaActiva,
        busqueda,
        setBusqueda,
        categoriasDisponibles,
        menuFiltrado,
        totalItems: menu.length,
        itemsFiltradosCount: menuFiltrado.length,
    };
};
