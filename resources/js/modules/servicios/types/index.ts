export interface PoliticaItem {
    id: number;
    nombre: string;
    descripcion?: string;
    tipo?: string;
}

export interface ServicioItem {
    id: number;
    codigo?: string;
    slug?: string;
    nombre: string;
    descripcion?: string;
    categoria?: string;
    precio?: number | null;
    moneda?: string;
    imagen?: string;
    imagenes?: string[];
    icono?: string;
    politicas?: PoliticaItem[];
    destacado?: boolean;
}

export interface ServiciosPageProps {
    services?: ServicioItem[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}

export interface ServicioDetalleProps {
    service: ServicioItem;
    relacionados?: ServicioItem[];
}
