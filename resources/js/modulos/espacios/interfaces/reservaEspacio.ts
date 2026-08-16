import type { OpcionesReserva } from '@/modulos/reservas/interfaces/opcionesReserva';

export interface EspacioReservable {
    id: number;
    codigo: string;
    slug: string;
    nombre: string;
    tipo: string;
    tipo_label: string;
    descripcion: string;
    ubicacion: string;
    precio: number;
    precio_por_hora?: number;
    precio_base?: number;
    es_oferta?: boolean;
    tipo_tarifa_label?: string;
    moneda: string;
    capacidad: number;
    web: boolean;
    reservable: boolean;
    es_restaurante: boolean;
    imagenes: string[];
    meta_datos?: Record<string, string | number | boolean | null>;
    serviciosIncluidos?: string[];
    politicas?: Array<{
        id?: number;
        nombre: string;
        descripcion: string;
    }>;
}

export interface PropiedadesReservarEspacio {
    space: EspacioReservable;
    opcionesReserva: OpcionesReserva;
}
