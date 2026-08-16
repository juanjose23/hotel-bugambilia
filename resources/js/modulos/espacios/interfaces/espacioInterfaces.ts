export interface SubEspacioItem {
    id: number;
    codigo: string;
    slug: string;
    nombre: string;
    capacidad: number;
    reservable: boolean;
}

export interface EspacioItem {
    id: number;
    codigo: string;
    slug?: string;
    nombre: string;
    tipo: string;
    tipo_label: string;
    capacidad: number;
    descripcion: string;
    precio?: number | string;
    precio_base?: number | string;
    precio_hora?: number | string;
    precio_desde?: number | string;
    moneda?: string;
    ubicacion: string;
    web?: boolean;
    reservable: boolean;
    imagenes?: string[];
    es_restaurante?: boolean;
    sub_espacios?: SubEspacioItem[];
    meta_datos?: {
        metros_cuadrados?: number | string;
        equipamiento_incluido?: string[];
        tipo_cocina?: string;
        tipo_servicio?: string;
        horario_comida?: string;
        capacidad_mesas?: number | string;
        restricciones_gimnasio?: string;
        caracteristicas?: string[];
    };
    serviciosIncluidos?: Array<
        | string
        | {
              nombre: string;
              descripcion?: string | null;
              icono?: string | null;
              incluido?: boolean | null;
          }
    >;
    politicas?: Array<{
        id?: number;
        nombre: string;
        descripcion: string;
    }>;
}

export interface TipoItem {
    tipo: string;
    label: string;
}

export interface PropiedadesSeccionEspacios {
    espacios?: EspacioItem[];
    tipos?: TipoItem[];
    tipoSeleccionado?: string | null;
}

export interface PropiedadesTarjetaEspacio {
    espacio: EspacioItem;
    onVerGaleria?: (espacio: EspacioItem) => void;
}

export interface SimilarSpace {
    id: number;
    slug?: string;
    nombre: string;
    tipo: string;
    precio: number;
    moneda: string;
    imagen: string;
}

export interface PropiedadesSeccionDetalleEspacio {
    space: EspacioItem;
    similarSpaces?: SimilarSpace[];
}
