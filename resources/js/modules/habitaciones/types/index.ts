import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
} from '@/modules/reservas/types';
import type { RoomItem } from '@/modules/shared/types';

export interface PaginacionData {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export interface HabitacionesPageProps {
    rooms?: RoomItem[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: PaginacionData;
}

export interface ServicioIncluidoItem {
    nombre: string;
    descripcion?: string;
    icono?: string;
    incluido: boolean;
}

export interface PoliticaItem {
    id: number | string;
    nombre: string;
    descripcion?: string | null;
    tipo?: string;
}

export interface EquipamientoItem {
    nombre: string;
    categoria?: string;
    cantidad?: number;
}

export interface HabitacionDetalleData extends RoomItem {
    categoria_id?: number;
    ubicacion_id?: number;
    ubicacion?: string;
    adultos?: number;
    ninos?: number;
    medidas?: string;
    vistas?: string[];
    serviciosIncluidos?: ServicioIncluidoItem[];
    politicas?: PoliticaItem[];
    equipamiento?: (string | EquipamientoItem)[];
}

export interface HabitacionDetalleProps {
    room: HabitacionDetalleData;
    similarRooms?: RoomItem[];
    serviciosDisponibles?: ServicioAdicionalItem[];
    beneficiosCliente?: BeneficioClienteItem[];
    diasAgotados?: string[];
}

export interface FiltrosHabitacionesState {
    categoria: string;
    buscar: string;
    huespedes: string;
    orden: 'precio_asc' | 'precio_desc' | 'popular';
}
