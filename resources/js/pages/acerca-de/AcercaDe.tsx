import { Head } from '@inertiajs/react';
import { SeccionAcercaDe } from '@/modulos/acerca-de/componentes/SeccionAcercaDe';

interface PropiedadesPaginaAcercaDe {
    hotel?: {
        name?: string;
        fundado?: string | number;
        direccion?: string;
        telefono?: string;
        email?: string;
    };
}

export const PaginaAcercaDe = ({ hotel }: PropiedadesPaginaAcercaDe) => {
    return (
        <>
            <Head>
                <title>Acerca de Nosotros — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Conozca Hotel Bugambilias en Estelí. Más de 35 años ofreciendo hospitalidad boutique, garantía de mejor tarifa y experiencia corporativa inolvidable."
                />
            </Head>
            <SeccionAcercaDe hotelInfo={hotel} />
        </>
    );
};

export default PaginaAcercaDe;
