export interface RoomItem {
    id: number;
    codigo: string;
    numero: string | number;
    slug: string;
    nombre: string;
    descripcion?: string;
    categoria?: string;
    precio: number;
    precio_desde?: number;
    moneda: string;
    capacidad?: number;
    camas?: string;
    imagen: string;
    imagenes?: string[];
    servicios?: { id: number | string; nombre: string }[];
    servicios_ids?: (number | string)[];
}
