import type { OpcionesReserva } from '@/modulos/reservas/interfaces/opcionesReserva';

export interface HabitacionReservable {
    id: number;
    categoria_id?: number;
    ubicacion_id?: number;
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
    diasAgotadosHabitacion?: string[];
    ocupacionHabitacionPorDia?: Record<
        string,
        {
            ocupadas: number;
            total: number;
            disponibles: number;
            agotado: boolean;
        }
    >;
    totalHabitacionesCategoria?: number;
}
