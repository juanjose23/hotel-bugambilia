export interface InformacionHotelInicio {
    nombre?: string;
    telefono?: string;
    email?: string;
    direccion?: string;
}

export interface HabitacionInicio {
    id: number;
    codigo: string;
    numero: number;
    slug: string;
    nombre: string;
    descripcion: string;
    categoria: string;
    precio: number;
    moneda: string;
    capacidad: number;
    camas: string;
    imagen: string;
}

export interface ServicioInicio {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    categoria: string;
    precio?: number | null;
    moneda: string;
    imagen: string;
}

export interface PromocionInicio {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    badge: string;
    precio_paquete?: number | null;
    precio_final?: number | null;
    descuento_porcentaje?: number | null;
    descuento_monto?: number | null;
    moneda: string;
    imagen?: string | null;
    itemsIncluidos?: string[];
    url_reserva?: string;
    habitacion_slug?: string;
    valido_hasta?: string;
}

export interface TestimonioInicio {
    id: number;
    nombreAutor: string;
    paisAutor: string;
    avatarAutor?: string;
    comentario: string;
    calificacion: number;
    tipoEstancia: string;
    fecha: string;
}

export interface PropiedadesPaginaInicio {
    hotelInfo?: InformacionHotelInicio;
    habitaciones?: HabitacionInicio[];
    servicios?: ServicioInicio[];
    promociones?: PromocionInicio[];
    categoriasHabitacion?: string[];
    testimonios?: TestimonioInicio[];
}
