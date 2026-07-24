export interface LinkPaginacion {
    url: string | null;
    label: string;
    active: boolean;
}
export interface DatosPaginacion {
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
export interface ItemPolitica {
    id?: number;
    nombre: string;
    descripcion?: string | null;
    tipo?: string;
}
export interface ItemServicio {
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
    politicas?: ItemPolitica[];
}
export interface ItemHabitacion {
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
    politicas?: ItemPolitica[];
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
export interface ItemEspacio {
    id: number;
    nombre: string;
    descripcion?: string | null;
    tipo?: string;
    capacidad?: number;
    precio?: number | null;
    moneda?: string;
    imagenes?: string[];
    imagen?: string;
    metadatos?: Record<string, string | number | boolean | null>;
    serviciosIncluidos?: string[];
    equipamiento?: string[];
    politicas?: ItemPolitica[];
}
export interface Acompanante {
    nombre: string;
    identificacion?: string;
}
export interface ItemReserva {
    id: number;
    codigo_reserva: string;
    nombre_cliente: string;
    tipo_reserva: string;
    tipo_reserva_label: string;
    estado: string;
    estado_label: string;
    estado_color: string;
    fecha_check_in: string;
    fecha_check_out?: string;
    hora_reserva?: string;
    adultos: number;
    ninos: number;
    total: number;
    detalles: string;
    notas?: string;
    acompanantes?: Acompanante[];
}
export interface DatosRestaurante {
    id: number;
    nombre: string;
    descripcion?: string | null;
    horarios?: string | null;
    imagen?: string | null;
}
export interface DatosAmbiente {
    id: number;
    nombre: string;
    descripcion?: string | null;
    capacidad?: number;
    imagenes?: string[];
}
export interface DatosMesa {
    id: number;
    numero: number;
    capacidad: number;
    ambiente_id?: number;
}
export interface DatosMenuItem {
    id: number;
    nombre: string;
    descripcion?: string | null;
    precio: number;
    moneda?: string;
    categoria?: string;
    imagen?: string | null;
}
export interface ServicioExtra {
    id: string;
    nombre: string;
    descripcion: string;
    precio: number;
    icono?: string;
    imagen?: string;
}
export interface DatosReserva {
    habitacion: string;
    ubicacion: string;
    imagen: string;
    calificacion: number;
    fechaEntrada: string;
    fechaSalida: string;
    noches: number;
    huespedes: number;
    precioHabitacion: number;
    impuestos: number;
    tarifaServicio: number;
    total: number;
}
