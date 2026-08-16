import type { ComponentType } from 'react';

export interface ElementoGaleria {
    id: number;
    titulo?: string;
    title?: string;
    categoria?: string;
    category?: string;
    imagen?: string;
    src?: string;
    alt?: string;
    descripcion?: string;
}

export interface ItemValorHotel {
    id: number;
    titulo: string;
    descripcion: string;
    icono: ComponentType<{ className?: string }> | string;
}

export interface HitoHistoria {
    ano: string;
    titulo: string;
    descripcion: string;
}

export interface PropiedadesGaleriaHotel {
    elementos?: ElementoGaleria[];
    items?: ElementoGaleria[];
}

export interface PilarConfianza {
    titulo: string;
    descripcion: string;
    icono: ComponentType<{ className?: string }>;
    destacado?: string;
}

export interface EstadisticaHotel {
    valor: string;
    etiqueta: string;
    icono: ComponentType<{ className?: string }>;
}

export interface PropiedadesTarjetaEstadisticaItem {
    valor: string;
    etiqueta: string;
    Icono: ComponentType<{ className?: string }>;
}

export interface PropiedadesSeccionAcercaDe {
    hotelInfo?: {
        name?: string;
        fundado?: string | number;
        direccion?: string;
        telefono?: string;
        email?: string;
    };
}
