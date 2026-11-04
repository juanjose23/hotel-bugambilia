import { useMemo, useState } from 'react';
import { useWatch } from 'react-hook-form';
import type { UseFormReturn } from 'react-hook-form';
import type { FormReservaMesa } from '../schemas/reservaMesaSchema';
import type { MenuItemData } from '../types';

export interface PlatoPreorden {
    plato: MenuItemData;
    cantidad: number;
}

export const useReservaFlujoReserva = (
    menu: MenuItemData[],
    form: UseFormReturn<FormReservaMesa>,
) => {
    const [pasoActual, setPasoActual] = useState<1 | 2 | 3>(1);
    const items =
        useWatch({ control: form.control, name: 'items_preorden' }) ?? [];
    const platosPreorden: PlatoPreorden[] = items.flatMap((item) => {
        const plato = menu.find((p) => p.id === item.plato_id);

        return plato ? [{ plato, cantidad: item.cantidad }] : [];
    });
    const [categoriaMenuFiltro, setCategoriaMenuFiltro] =
        useState<string>('todos');

    const agregarPlatoPreorden = (plato: MenuItemData) => {
        const actuales = form.getValues('items_preorden');
        const existente = actuales.find((item) => item.plato_id === plato.id);
        form.setValue(
            'items_preorden',
            existente
                ? actuales.map((item) =>
                      item.plato_id === plato.id
                          ? {
                                ...item,
                                cantidad: Math.min(50, item.cantidad + 1),
                            }
                          : item,
                  )
                : [...actuales, { plato_id: plato.id, cantidad: 1 }],
            { shouldValidate: true, shouldDirty: true },
        );
    };
    const removerPlatoPreorden = (platoId: number) => {
        form.setValue(
            'items_preorden',
            form
                .getValues('items_preorden')
                .map((item) =>
                    item.plato_id === platoId
                        ? { ...item, cantidad: item.cantidad - 1 }
                        : item,
                )
                .filter((item) => item.cantidad > 0),
            { shouldValidate: true, shouldDirty: true },
        );
    };
    const irPaso = async (paso: 1 | 2 | 3) => {
        if (
            paso > pasoActual &&
            !(await form.trigger([
                'fecha',
                'hora',
                'duracion_horas',
                'adultos',
                'items_preorden',
            ]))
        ) {
            return;
        }

        setPasoActual(paso);
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
        irPaso,
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
