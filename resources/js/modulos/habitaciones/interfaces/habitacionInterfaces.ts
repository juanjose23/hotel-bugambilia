export interface HabitacionGrupo {
    id: number;
    nombre?: string;
    name?: string;
    categoria?: string;
    type?: string;
    capacidad?: number | string;
    guests?: string | number;
    precio?: number | string;
    precio_desde?: number | string;
    precio_base?: number | string;
    price?: number | string;
    precio_noche?: number | string;
    camas?: string;
    beds?: string;
    vista?: string;
    vistas?: string;
    imagenes?: string[];
    image?: string;
    imagen?: string;
    imagen_portada?: string;
    slug?: string;
    descripcion?: string;
    desc?: string;
    popular?: boolean;
    moneda?: string;
    disponibles?: number;
    total?: number;
}

export type RoomItem = HabitacionGrupo;

export interface PropiedadesTarjetaHabitacion {
    habitacion?: HabitacionGrupo;
    room?: HabitacionGrupo;
    onReservar?: (room: HabitacionGrupo) => void;
}
