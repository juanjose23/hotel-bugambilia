import type { ItemServicio, DatosPaginacion } from '@/modulos/compartido/types';

export type { ItemServicio };

export interface PropiedadesSeccionServicios {
    services?: ItemServicio[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}

export interface PropiedadesTarjetaServicioItem {
    servicio: ItemServicio;
}

export interface PropiedadesSeccionDetalleServicio {
    service: ItemServicio & {
        imagenes: string[];
    };
}
