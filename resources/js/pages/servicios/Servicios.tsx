import { Head } from '@inertiajs/react';
import { SeccionServicios } from '@/modules/services/components/SeccionServicios';
import type { ItemServicio, DatosPaginacion } from '@/modules/shared/types';
interface ServiciosProps {
    services: ItemServicio[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}
const PaginaServicios = ({
    services,
    categorias = [],
    categoriaMasPopular = null,
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: ServiciosProps) => {
    return (
        <>
            <Head>
                <title>Servicios — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Servicios premium del Hotel Bugambilias — Restaurante, piscina, gym y transporte. Descubre todo lo que ofrecemos en Estelí."
                />
            </Head>
            <SeccionServicios
                services={services}
                categorias={categorias}
                categoriaMasPopular={categoriaMasPopular}
                selectedCategory={selectedCategory}
                searchQuery={searchQuery}
                pagination={pagination}
            />
        </>
    );
};
export default PaginaServicios;
