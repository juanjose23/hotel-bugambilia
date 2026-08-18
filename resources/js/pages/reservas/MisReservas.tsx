import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { LayoutPortalCliente } from '@/modulos/portal/componentes/layouts/LayoutPortalCliente';
import { SeccionPortalMisReservas } from '@/modulos/portal/componentes/SeccionPortalMisReservas';
import { mapearReservaClienteDomain } from '@/modulos/reservas/domain/mapeadoresReserva';

interface PropiedadesMisReservas {
    reservas?: Record<string, unknown>[];
    codigoBusqueda?: string;
}

export const PaginaMisReservas = ({
    reservas = [],
}: PropiedadesMisReservas) => {
    const reservasMapped: ReservaClienteDomain[] = reservas.map((r) =>
        mapearReservaClienteDomain(r),
    );

    return (
        <>
            <Head>
                <title>Portal de Huéspedes — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Consulte y gestione sus reservaciones activas y pasadas en Hotel Bugambilias Estelí."
                />
            </Head>
            <SeccionPortalMisReservas reservas={reservasMapped} />
        </>
    );
};

PaginaMisReservas.layout = (page: ReactNode) => (
    <LayoutPortalCliente>{page}</LayoutPortalCliente>
);

export default PaginaMisReservas;
