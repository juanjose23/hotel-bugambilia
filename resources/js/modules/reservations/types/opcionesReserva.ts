export interface OpcionReserva {
    id: number;
    nombre: string;
    descripcion?: string | null;
    precio?: number;
    moneda?: string;
    codigo?: string;
    descuento?: string;
}

export interface OpcionesReserva {
    servicios: OpcionReserva[];
    espacios: OpcionReserva[];
    promociones: OpcionReserva[];
}
