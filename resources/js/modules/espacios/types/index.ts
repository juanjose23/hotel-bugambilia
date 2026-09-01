export interface PoliticaEspacio {
    id: number;
    nombre: string;
    descripcion?: string;
}

export interface ServicioIncluidoEspacio {
    id: number;
    nombre: string;
    descripcion?: string;
    icono?: string;
}

export interface SubEspacioItem {
    id: number;
    codigo?: string;
    slug?: string;
    nombre: string;
    capacidad?: number;
    reservable?: boolean;
}

export interface EspacioItem {
    id: number;
    codigo?: string;
    slug?: string;
    nombre: string;
    tipo: string;
    tipo_label?: string;
    capacidad?: number;
    descripcion?: string;
    ubicacion?: string;
    web?: boolean;
    reservable?: boolean;
    es_restaurante?: boolean;
    imagenes?: string[];
    meta_datos?: Record<string, unknown>;
    sub_espacios?: SubEspacioItem[];
    precio?: number;
    precio_por_hora?: number;
    precio_base?: number;
    es_oferta?: boolean;
    tipo_tarifa_label?: string;
    moneda?: string;
    serviciosIncluidos?: ServicioIncluidoEspacio[];
    politicas?: PoliticaEspacio[];
}

export interface EspacioSimilarItem {
    id: number;
    slug?: string;
    nombre: string;
    tipo?: string;
    precio?: number;
    moneda?: string;
    imagen?: string;
}

export interface TipoEspacioOpcion {
    tipo: string;
    label: string;
}

export interface FiltrosEspaciosState {
    tipo: string;
    capacidadMinima: number;
    buscar: string;
}

export interface EspaciosPageProps {
    espacios?: EspacioItem[];
    tipos?: TipoEspacioOpcion[];
    tipoSeleccionado?: string;
}

export interface EspacioDetalleProps {
    space: EspacioItem;
    similarSpaces?: EspacioSimilarItem[];
    opcionesReserva?: Record<string, unknown>;
}

export interface EspacioReservarProps {
    space: EspacioItem;
    similarSpaces?: EspacioSimilarItem[];
    opcionesReserva?: Record<string, unknown>;
}
