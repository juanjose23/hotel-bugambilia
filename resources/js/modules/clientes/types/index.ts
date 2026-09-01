export interface ClienteProfile {
    id: number;
    usuario_id: number;
    nombre: string;
    email: string;
    telefono?: string | null;
    identificacion?: string | null;
    tipo_identificacion?: string | null;
    codigo_cliente?: string | null;
    tipo_cliente?: string;
    avatar?: string | null;
}

export interface EstadisticasHuesped {
    total_reservas: number;
    activas: number;
    completadas: number;
}

export interface PortalReservaResumen {
    id: number;
    codigo_reserva: string;
    estado: number;
    estado_label: string;
    tipo_reserva: string;
    tipo_reserva_label: string;
    fecha_check_in?: string | null;
    fecha_check_out?: string | null;
    noches: number;
    adultos: number;
    ninos: number;
    total: number;
    total_pagado: number;
    saldo: number;
    moneda_simbolo: string;
    recurso: {
        id?: number;
        nombre: string;
        categoria: string;
        imagen?: string | null;
    };
    puede_cancelar: boolean;
    url_voucher: string;
}

export interface AcompananteItem {
    nombre: string;
    identificacion?: string;
    tipo: 'adulto' | 'nino' | 'bebe';
}

export interface ConsumoCuentaItem {
    id: number;
    concepto: string;
    descripcion?: string | null;
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    total: number;
    created_at?: string;
}

export interface PagoCuentaItem {
    id: number;
    monto: number;
    forma_pago: string;
    estado: number;
    fecha_pago?: string;
}

export interface CuentaEstanciaDetalle {
    id: number;
    numero_cuenta: string;
    estado: number;
    subtotal: number;
    impuesto_total: number;
    descuento_total: number;
    total: number;
    total_pagado: number;
    saldo: number;
    consumos: ConsumoCuentaItem[];
    pagos: PagoCuentaItem[];
}

export interface PortalReservaDetalleCompleto {
    id: number;
    codigo_reserva: string;
    estado: number;
    estado_label: string;
    tipo_reserva: string;
    tipo_reserva_label: string;
    nombre_cliente: string;
    email_cliente: string;
    telefono_cliente?: string | null;
    fecha_check_in?: string | null;
    fecha_check_out?: string | null;
    hora_reserva?: string | null;
    noches: number;
    adultos: number;
    ninos: number;
    total: number;
    total_pagado: number;
    saldo: number;
    moneda: {
        id?: number;
        codigo: string;
        simbolo: string;
        nombre: string;
    };
    recurso: {
        id?: number;
        nombre: string;
        categoria: string;
        codigo?: string;
        imagenes: Array<{ url: string; es_portada?: boolean }>;
        servicios_incluidos: Array<{ id: number; nombre: string }>;
    };
    acompanantes: AcompananteItem[];
    cuenta?: CuentaEstanciaDetalle | null;
    puede_cancelar: boolean;
    puede_solicitar_servicios: boolean;
    url_voucher: string;
    url_pago_saldo?: string | null;
}

export interface CatalogoServicioItemData {
    id: number;
    nombre: string;
    codigo: string;
    descripcion?: string | null;
    categoria: string;
    categoria_id?: number;
    precio: number;
    moneda_simbolo: string;
    imagen?: string | null;
    requiere_reserva: boolean;
}
