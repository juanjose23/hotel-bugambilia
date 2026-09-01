import type {
    FiltrosDisponiblesDomain,
    RoomItem,
} from '@/modules/shared/types';

export interface ServicioHomeItem {
    id: number;
    codigo?: string;
    slug?: string;
    nombre: string;
    descripcion: string;
    categoria: string;
    imagen: string;
}

export interface EspacioHomeItem {
    id: number;
    codigo?: string;
    slug?: string;
    nombre: string;
    descripcion: string;
    categoria?: string;
    capacidad: number;
    precio_base?: number;
    imagen: string;
}

export interface PropiedadesHomePage {
    hotelInfo?: Record<string, unknown>;
    habitaciones?: RoomItem[];
    servicios?: ServicioHomeItem[];
    espacios?: EspacioHomeItem[];
    filtrosDisponibles?: FiltrosDisponiblesDomain;
}
