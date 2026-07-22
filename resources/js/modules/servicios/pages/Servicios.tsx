import type { ServicioItem, PaginacionData } from '@/modules/compartido/tipos';
import SeccionServicios from '@/modules/servicios/components/SeccionServicios';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface ServiciosProps {
    services: ServicioItem[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: PaginacionData;
}

export default function Servicios({
    services,
    categorias = [],
    categoriaMasPopular = null,
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: ServiciosProps) {
    return (
        <LayoutPublico>
            <SeccionServicios
                services={services}
                categorias={categorias}
                categoriaMasPopular={categoriaMasPopular}
                selectedCategory={selectedCategory}
                searchQuery={searchQuery}
                pagination={pagination}
            />
        </LayoutPublico>
    );
}
