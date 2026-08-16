export interface AcompananteCliente {
    id?: number;
    nombre: string;
    identificacion?: string;
    tipo?: string;
}

export interface ReservaClienteDomain {
    id: number;
    codigo_reserva: string;
    nombre_cliente: string;
    email_cliente?: string | null;
    telefono_cliente?: string | null;
    tipo_reserva: string;
    tipo_reserva_label: string;
    estado: number;
    estado_label: string;
    estado_color: string;
    fecha_check_in: string;
    fecha_check_out?: string | null;
    hora_reserva?: string | null;
    adultos: number;
    ninos: number;
    total: number | string;
    detalles: string;
    notas?: string;
    huespedes_count?: number;
    can_generar_voucher?: boolean;
    acompanantes?: AcompananteCliente[];
}

export interface ClienteDomain {
    id: number;
    nombre: string;
    apellidos?: string;
    nombre_completo: string;
    identificacion?: string;
    telefono?: string;
    email?: string;
    direccion?: string;
    reservas?: ReservaClienteDomain[];
}
