import type { OpcionesReserva } from '@/modules/reservations/types/opcionesReserva';

export interface HabitacionReservable {
    id: number;
    codigo: string;
    slug: string;
    nombre: string;
    numero: string;
    descripcion: string;
    categoria: string;
    ubicacion: string;
    precio: number;
    moneda: string;
    capacidad: number;
    adultos: number;
    ninos: number;
    medidas: string;
    vistas: string[];
    camas: string;
    imagenes: string[];
    serviciosIncluidos?: string[];
    politicas?: Array<{
        id?: number;
        nombre: string;
        descripcion: string;
    }>;
    equipamiento?: string[];
}

export interface PropiedadesReservarHabitacion {
    room: HabitacionReservable;
    opcionesReserva: OpcionesReserva;
}
