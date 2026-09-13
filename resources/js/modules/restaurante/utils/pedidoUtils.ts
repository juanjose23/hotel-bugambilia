import { router } from '@inertiajs/react';
import type { ItemCarritoRestaurante } from '../types';

export interface ItemPedidoPayload {
    plato_id: number;
    cantidad: number;
    observaciones: string | null;
}

export const mapearItemsParaPedido = (
    items: ItemCarritoRestaurante[],
): ItemPedidoPayload[] => {
    return items.map((i) => ({
        plato_id: i.plato.id,
        cantidad: i.cantidad,
        observaciones: i.notas || null,
    }));
};

export const abrirWhatsappSiExiste = (url?: string | null): void => {
    if (url) {
        window.open(url, '_blank');
    }
};

export const redirigirAlPortalPedidos = (demoraMs = 1500): void => {
    setTimeout(() => {
        router.visit('/portal/pedidos');
    }, demoraMs);
};
