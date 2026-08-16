import { Head } from '@inertiajs/react';
import { SeccionAutoCheckIn } from '@/modulos/reservas/componentes/SeccionAutoCheckIn';
import type { ReservaAutoCheckInProps } from '@/modulos/reservas/interfaces/autoCheckInInterfaces';

interface PropiedadesPaginaAutoCheckIn {
    reserva?: ReservaAutoCheckInProps;
    politicas?: Array<{ id: number; nombre: string; descripcion: string }>;
}

export const PaginaAutoCheckIn = ({
    reserva,
    politicas = [],
}: PropiedadesPaginaAutoCheckIn) => {
    return (
        <>
            <Head>
                <title>Auto Check-in Digital — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Pre-registro digital anticipado para su estancia en Hotel Bugambilias Estelí. Agilice su llegada en recepción."
                />
            </Head>
            <SeccionAutoCheckIn reserva={reserva} politicas={politicas} />
        </>
    );
};

export default PaginaAutoCheckIn;
