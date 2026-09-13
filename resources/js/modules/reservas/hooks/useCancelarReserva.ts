import { router } from '@inertiajs/react';

interface UseCancelarReservaOptions {
    confirmMessage?: string;
}

const MENSAJE_DEFECTO = '¿Estás seguro de que deseas cancelar tu reserva?';

export const useCancelarReserva = ({
    confirmMessage = MENSAJE_DEFECTO,
}: UseCancelarReservaOptions = {}) => {
    const cancelarReserva = (reservaId: number, codigoReserva?: string) => {
        if (!confirm(confirmMessage)) {
            return;
        }

        router.post(
            `/reservas/${reservaId}/cancelar`,
            {
                codigo: codigoReserva,
            },
            {
                preserveScroll: true,
            },
        );
    };

    return { cancelarReserva };
};

export default useCancelarReserva;
