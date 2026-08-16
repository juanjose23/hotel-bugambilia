export interface HuespedInput {
    nombre: string;
    identificacion: string;
    tipo: 'adulto' | 'nino' | 'infante';
    esTitular: boolean;
}

export interface ReservaAutoCheckInProps {
    codigoReserva: string;
    clienteNombre: string;
    clienteEmail: string;
    clienteTelefono: string;
    habitacionNombre?: string;
    categoriaHabitacion?: string;
    capacidadAdultos: number;
    capacidadNinos: number;
    fechaEntrada: string;
    fechaSalida: string;
    solicitaCuentaInicial?: boolean;
    limiteCuentaInicial?: number;
}

export interface PropiedadesSeccionAutoCheckIn {
    reserva?: ReservaAutoCheckInProps;
    politicas?: Array<{ id: number; nombre: string; descripcion: string }>;
}
