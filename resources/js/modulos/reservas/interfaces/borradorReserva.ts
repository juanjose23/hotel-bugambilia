export interface ServicioSeleccionadoReserva {
    servicio_id: number;
    cantidad: number;
}

export interface EspacioSeleccionadoReserva {
    espacio_id: number;
    cantidad: number;
}

interface DatosComunesBorradorReserva {
    nombre_cliente: string;
    telefono_cliente: string;
    email_cliente: string;
    fecha_check_in: string;
    adultos: number;
    ninos: number;
    notas: string;
    servicios_adicionales: ServicioSeleccionadoReserva[];
    espacios_adicionales: EspacioSeleccionadoReserva[];
    promocion_id: number | null;
    solicita_cuenta: boolean;
    limite_cuenta_solicitado: number | null;
    tipo_pago_reserva: 'sin_pago' | 'abono_50' | 'pago_completo';
    canal_pago_reserva: 'manual' | 'stripe' | 'transferencia' | 'sin_pago';
    origen_pago_reserva: 'publico' | 'admin';
    metodo_pago_reserva: number | null;
}

export interface DatosBorradorHabitacion extends DatosComunesBorradorReserva {
    tipo_reserva: 'habitacion';
    habitacion_id: string;
    fecha_check_out: string;
}

export interface DatosBorradorEspacio extends DatosComunesBorradorReserva {
    tipo_reserva: 'restaurante';
    espacio_id: string;
    hora_reserva: string;
    hora_fin: string;
}

export interface BorradorHabitacion {
    tipo: 'habitacion';
    rutaRetorno: string;
    pasoActual: number;
    datos: DatosBorradorHabitacion;
}

export interface BorradorEspacio {
    tipo: 'espacio';
    rutaRetorno: string;
    pasoActual: number;
    horaInicio: string;
    horaFin: string;
    datos: DatosBorradorEspacio;
}

export type BorradorReserva = BorradorHabitacion | BorradorEspacio;
