import { Head } from '@inertiajs/react';
import type { ItemServicio, DatosPaginacion } from '@/modulos/compartido/types';
import { SeccionServicios } from '@/modulos/servicios/componentes/SeccionServicios';

interface PropiedadesPaginaServicios {
    services: ItemServicio[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}

export const PaginaServicios = ({
    services,
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: PropiedadesPaginaServicios) => {
    return (
        <>
            <Head>
                <title>Servicios Exclusivos — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Servicios premium del Hotel Bugambilias en Estelí — Restaurante gourmet, piscina, gym, atención 24/7 y transporte ejecutivo."
                />
            </Head>
            <SeccionServicios
                services={services}
                categorias={categorias}
                selectedCategory={selectedCategory}
                searchQuery={searchQuery}
                pagination={pagination}
            />
        </>
    );
};

export default PaginaServicios;
