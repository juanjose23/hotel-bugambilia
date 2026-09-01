export interface PoliticaReserva {
    id?: number;
    titulo?: string;
    nombre?: string;
    descripcion?: string;
    porcentaje?: number;
    dias_anticipacion?: number;
    penalizaciones?: {
        id?: number;
        dias_anticipacion?: number;
        porcentaje?: number;
        descripcion?: string;
    }[];
}

export interface BeneficioClienteItem {
    id: number;
    nombre?: string;
    tipo:
        | 'descuento_reserva'
        | 'anticipo_reducido'
        | 'cortesia'
        | 'late_checkout'
        | 'upgrade_habitacion'
        | 'descuento_restaurante';
    valor: number;
    es_porcentaje: boolean;
    descripcion?: string;
    combinable?: boolean;
}

export interface ServicioAdicionalItem {
    id: number;
    nombre: string;
    precio: number;
    moneda?: string;
    descripcion?: string;
    icono?: string;
    categoria?: string;
}

export interface StripePaymentData {
    client_secret: string;
    publishable_key: string;
    transaccion_id?: number;
    monto: number;
    moneda: string;
}

export interface ReservaCreadaResponse {
    id: number;
    codigo_reserva: string;
    tipo_pago: string;
    estado?: string;
    total?: number;
    moneda?: string;
    fecha_check_in?: string;
    fecha_check_out?: string;
    habitacion_nombre?: string;
}

export interface ReservaPortalItem {
    id: number;
    codigo_reserva: string;
    estado: string;
    estado_label?: string;
    tipo_reserva: string;
    fecha_check_in: string;
    fecha_check_out?: string;
    hora_reserva?: string;
    total: number;
    total_pagado: number;
    saldo: number;
    moneda: string;
    habitacion?: {
        id: number;
        nombre: string;
        categoria?: string;
        imagen_principal?: string;
    };
    espacio?: {
        id: number;
        nombre: string;
    };
    servicios_incluidos?: {
        id: number;
        nombre: string;
        cantidad?: number;
        precio?: number;
    }[];
    beneficios_aplicados?: BeneficioClienteItem[];
    puede_cancelar?: boolean;
    url_voucher?: string;
    created_at?: string;
}
