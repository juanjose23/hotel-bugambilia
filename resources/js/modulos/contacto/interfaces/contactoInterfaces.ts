export interface DatosFormularioContacto {
    firstName: string;
    lastName: string;
    email: string;
    phone?: string;
    subject: string;
    message: string;
}

export interface BloqueInformacionContacto {
    icon: string;
    title: string;
    lines: string[];
}

export interface AmenidadContacto {
    icon: string;
    text: string;
}

export interface PreguntaFrecuente {
    question: string;
    answer: string;
}

export interface PropiedadesInformacionHotelContacto {
    nombre?: string;
    telefono?: string;
    email?: string;
    direccion?: string;
}

export interface PropiedadesSeccionContacto {
    hotelInfo?: PropiedadesInformacionHotelContacto;
}
