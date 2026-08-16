import { Head } from '@inertiajs/react';
import type { ItemServicio } from '@/modulos/compartido/types';
import { SeccionDetalleServicio } from '@/modulos/servicios/componentes/SeccionDetalleServicio';

interface PropiedadesPaginaServicioDetalle {
    service: ItemServicio & {
        imagenes: string[];
    };
}

export const PaginaServicioDetalle = ({
    service,
}: PropiedadesPaginaServicioDetalle) => {
    return (
        <>
            <Head>
                <title>{`${service?.nombre || 'Detalle Servicio'} — Hotel Bugambilias Estelí`}</title>
                <meta
                    name="description"
                    content={`Conozca más sobre el servicio ${service?.nombre} en Hotel Bugambilias Estelí. Atención boutique y confort garantizado.`}
                />
            </Head>
            <SeccionDetalleServicio service={service} />
        </>
    );
};

export default PaginaServicioDetalle;
