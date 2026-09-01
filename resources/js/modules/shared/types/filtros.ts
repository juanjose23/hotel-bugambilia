export interface CategoriaFiltro {
    id: number | string;
    nombre: string;
    slug?: string;
    habitaciones_count?: number;
}

export interface ServicioFiltro {
    id: number | string;
    nombre: string;
    slug?: string;
    categoria?: string;
}

export interface FiltrosDisponiblesDomain {
    categorias: CategoriaFiltro[];
    servicios: ServicioFiltro[];
    capacidades: number[];
    precioMin: number;
    precioMax: number;
    vistas?: string[];
}

export interface FiltrosActivos {
    categoria: string;
    precioMin: number;
    precioMax: number;
    capacidad: number;
    serviciosIds: (string | number)[];
    vista?: string;
}
