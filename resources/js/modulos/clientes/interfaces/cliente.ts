export interface AcompananteCliente {
    id?: number;
    nombre: string;
    identificacion?: string;
    tipo?: string;
}

export interface ActivoHabitacion {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    categoria: string;
    estado: string;
}

export interface ServicioHabitacion {
    id: number;
    nombre: string;
    descripcion: string;
    incluido: boolean;
}

export interface CargoEstadoCuenta {
    id: number;
    fecha: string;
    descripcion: string;
    monto: number;
    categoria: string;
}

export interface EstadoCuentaDomain {
    cargos: CargoEstadoCuenta[];
    subtotal: number;
    impuestos: number;
    total: number;
    total_pagado: number;
    saldo_pendiente: number;
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
    activos_habitacion?: ActivoHabitacion[];
    servicios_habitacion?: ServicioHabitacion[];
    estado_cuenta?: EstadoCuentaDomain;
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
