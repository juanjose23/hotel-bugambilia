import { useMemo, useState } from 'react';
import { departamentosPorDefecto } from '../constants';
import type { ItemCarritoRestaurante, MenuItemData } from '../types';

export const STORAGE_KEY_CARRITO_RESTAURANTE =
    'hotel_bugambilia_carrito_restaurante';

const leerCarritoGuardado = (): ItemCarritoRestaurante[] => {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const guardado = localStorage.getItem(STORAGE_KEY_CARRITO_RESTAURANTE);

        if (guardado) {
            const parsed = JSON.parse(guardado);

            if (Array.isArray(parsed)) {
                return parsed;
            }
        }
    } catch {
        // Carrito corrupto o localStorage no disponible: se ignora.
    }

    return [];
};

const persistirCarrito = (items: ItemCarritoRestaurante[]): void => {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        localStorage.setItem(
            STORAGE_KEY_CARRITO_RESTAURANTE,
            JSON.stringify(items),
        );
    } catch {
        // Sin almacenamiento disponible: el carrito solo vive en memoria.
    }
};

interface UseCarritoRestauranteProps {
    costoDelivery?: number;
    pedidoMinimo?: number;
}

export const useCarritoRestaurante = ({
    costoDelivery = departamentosPorDefecto[0]?.costo_envio ?? 50,
    pedidoMinimo = 0,
}: UseCarritoRestauranteProps = {}) => {
    const [items, setItems] =
        useState<ItemCarritoRestaurante[]>(leerCarritoGuardado);
    const [sheetAbierto, setSheetAbierto] = useState<boolean>(false);

    const mutarCarrito = (siguiente: ItemCarritoRestaurante[]) => {
        setItems(siguiente);
        persistirCarrito(siguiente);
    };

    const agregarItem = (plato: MenuItemData, notasIniciales?: string) => {
        const index = items.findIndex((i) => i.plato.id === plato.id);

        if (index >= 0) {
            const siguientes = [...items];
            siguientes[index] = {
                ...siguientes[index],
                cantidad: siguientes[index].cantidad + 1,
                notas:
                    notasIniciales !== undefined
                        ? notasIniciales
                        : siguientes[index].notas,
            };
            mutarCarrito(siguientes);

            return;
        }

        mutarCarrito([
            ...items,
            { plato, cantidad: 1, notas: notasIniciales || '' },
        ]);
    };

    const disminuirItem = (platoId: number) => {
        const index = items.findIndex((i) => i.plato.id === platoId);

        if (index < 0) {
            return;
        }

        if (items[index].cantidad > 1) {
            const siguientes = [...items];
            siguientes[index] = {
                ...siguientes[index],
                cantidad: siguientes[index].cantidad - 1,
            };
            mutarCarrito(siguientes);

            return;
        }

        mutarCarrito(items.filter((i) => i.plato.id !== platoId));
    };

    const actualizarNotasItem = (platoId: number, notas: string) => {
        const siguientes = items.map((i) =>
            i.plato.id === platoId ? { ...i, notas } : i,
        );
        mutarCarrito(siguientes);
    };

    const quitarItem = (platoId: number) => {
        mutarCarrito(items.filter((i) => i.plato.id !== platoId));
    };

    const vaciarCarrito = () => {
        mutarCarrito([]);
    };

    const totalItems = useMemo(() => {
        return items.reduce((acc, curr) => acc + curr.cantidad, 0);
    }, [items]);

    const subtotal = useMemo(() => {
        return items.reduce((acc, curr) => {
            const precio = Number(curr.plato.precio || 0);

            return acc + precio * curr.cantidad;
        }, 0);
    }, [items]);

    const moneda = items[0]?.plato.moneda || 'C$';
    const total = subtotal + (items.length > 0 ? costoDelivery : 0);
    const cumpleMinimo = subtotal >= pedidoMinimo;

    const obtenerCantidadItem = (platoId: number): number => {
        const item = items.find((i) => i.plato.id === platoId);

        return item ? item.cantidad : 0;
    };

    return {
        items,
        sheetAbierto,
        setSheetAbierto,
        agregarItem,
        disminuirItem,
        actualizarNotasItem,
        quitarItem,
        eliminarItem: quitarItem,
        vaciarCarrito,
        totalItems,
        subtotal,
        costoDelivery,
        pedidoMinimo,
        total,
        moneda,
        cumpleMinimo,
        obtenerCantidadItem,
    };
};

export type RetornoCarritoRestaurante = ReturnType<
    typeof useCarritoRestaurante
>;
