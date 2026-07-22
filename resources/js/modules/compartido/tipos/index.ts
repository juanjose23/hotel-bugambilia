export interface LinkPaginacion {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginacionData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    first_page_url?: string | null;
    last_page_url?: string | null;
    links: LinkPaginacion[];
}

export interface PoliticaItem {
    id?: number;
    nombre: string;
    descripcion?: string | null;
    tipo?: string;
}

export interface ServicioItem {
    id: number | string;
    codigo?: string;
    slug?: string;
    nombre: string;
    descripcion?: string | null;
    categoria?: string;
    precio?: number | null;
    moneda?: string;
    imagen?: string | null;
    icono?: string | null;
    politicas?: PoliticaItem[];
}

export interface HabitacionItem {
    id: number | string;
    codigo?: string;
    numero?: number;
    slug?: string;
    nombre: string;
    descripcion?: string | null;
    categoria?: string;
    ubicacion?: string;
    precio: number;
    moneda?: string;
    capacidad?: number;
    adultos?: number;
    ninos?: number;
    medidas?: string;
    vistas?: string[];
    camas?: string;
    imagenes?: string[];
    imagen?: string;
    serviciosIncluidos?: string[];
    politicas?: PoliticaItem[];
    equipamiento?: string[];
    popular?: boolean;
}

export interface HabitacionSimilares {
    id: number;
    slug: string;
    nombre: string;
    categoria: string;
    precio: number;
    moneda: string;
    imagen: string;
}
