export interface PromocionItem {
    id: number | string;
    url_reserva?: string;
    noches_incluidas?: number | null;
    personas_incluidas?: number | null;
    anticipacion_horas?: number;
    condiciones?: string | null;
    codigo: string;
    slug: string;
    nombre: string;
    descripcion?: string;
    tipo: string;
    precio_original?: number | null;
    precio_final: number;
    descuento_porcentaje?: number | null;
    descuento_monto?: number | null;
    moneda: string;
    fecha_inicio?: string;
    fecha_fin?: string;
    imagen: string;
    imagenes?: string[];
    beneficios?: {
        id: number | string;
        titulo: string;
        descripcion?: string;
        tipo?: string;
        valor?: number | null;
    }[];
    items?: {
        id: number | string;
        tipo: string;
        nombre?: string;
        recurso_id?: number;
        precio_especial?: number | null;
        incluido: boolean;
    }[];
}

export interface PromocionesPageProps {
    promociones?: PromocionItem[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
}

export interface OpcionReservaPromocion {
    key: string;
    tipo: string;
    recurso_id: number;
    nombre: string;
    necesita_salida: boolean;
}
