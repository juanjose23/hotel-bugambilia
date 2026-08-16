import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import type { BorradorReserva } from '@/modulos/reservas/interfaces/borradorReserva';

interface EstadoReserva {
    borrador: BorradorReserva | null;
    guardarBorrador: (borrador: BorradorReserva) => void;
    limpiarBorrador: () => void;
}

export const useAlmacenReserva = create<EstadoReserva>()(
    persist(
        (establecer) => ({
            borrador: null,
            guardarBorrador: (borrador) => establecer({ borrador }),
            limpiarBorrador: () => establecer({ borrador: null }),
        }),
        {
            name: 'hotel-bugambilias-borrador-reserva',
            storage: createJSONStorage(() => sessionStorage),
        },
    ),
);
