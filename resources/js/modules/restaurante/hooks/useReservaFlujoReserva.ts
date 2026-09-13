import { useMemo, useState } from 'react';
import type { MenuItemData } from '../types';

export interface PlatoPreorden {
    plato: MenuItemData;
    cantidad: number;
}

export const useReservaFlujoReserva = (menu: MenuItemData[]) => {
    const [pasoActual, setPasoActual] = useState<1 | 2 | 3>(1);
    const [platosPreorden, setPlatosPreorden] = useState<PlatoPreorden[]>([]);
    const [categoriaMenuFiltro, setCategoriaMenuFiltro] =
        useState<string>('todos');

    const agregarPlatoPreorden = (plato: MenuItemData) => {
        setPlatosPreorden((prev) => {
            const index = prev.findIndex((p) => p.plato.id === plato.id);

            if (index >= 0) {
                const nuevo = [...prev];
                nuevo[index] = {
                    ...nuevo[index],
                    cantidad: nuevo[index].cantidad + 1,
                };

                return nuevo;
            }

            return [...prev, { plato, cantidad: 1 }];
        });
    };

    const removerPlatoPreorden = (platoId: number) => {
        setPlatosPreorden((prev) => {
            const index = prev.findIndex((p) => p.plato.id === platoId);

            if (index < 0) {
                return prev;
            }

            const item = prev[index];

            if (item.cantidad > 1) {
                const nuevo = [...prev];
                nuevo[index] = {
                    ...nuevo[index],
                    cantidad: nuevo[index].cantidad - 1,
                };

                return nuevo;
            }

            return prev.filter((p) => p.plato.id !== platoId);
        });
    };

    const categoriasMenu = useMemo(
        () => [
            'todos',
            ...Array.from(
                new Set(menu.map((m) => m.categoria || 'Especialidades')),
            ),
        ],
        [menu],
    );

    const menuFiltrado = useMemo(() => {
        if (categoriaMenuFiltro === 'todos') {
            return menu;
        }

        return menu.filter((item) => item.categoria === categoriaMenuFiltro);
    }, [menu, categoriaMenuFiltro]);

    const subtotalPreorden = useMemo(
        () =>
            platosPreorden.reduce(
                (acc, p) => acc + (p.plato.precio ?? 0) * p.cantidad,
                0,
            ),
        [platosPreorden],
    );

    return {
        pasoActual,
        irPaso: setPasoActual,
        platosPreorden,
        agregarPlatoPreorden,
        removerPlatoPreorden,
        categoriaMenuFiltro,
        alCambiarCategoria: setCategoriaMenuFiltro,
        categoriasMenu,
        menuFiltrado,
        subtotalPreorden,
    };
};
