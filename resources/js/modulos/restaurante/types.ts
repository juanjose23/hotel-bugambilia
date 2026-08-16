export interface RestauranteData {
    id: number;
    nombre: string;
    descripcion?: string;
    capacidad: number;
    imagenes: string[];
    tipo_cocina: string;
    tipo_servicio: string;
    horario_desayuno: string;
    horario_almuerzo: string;
    horario_cena: string;
}
export interface MesaData {
    id: number;
    nombre: string;
    capacidad: number;
    tipo_mesa: string;
    zona: string;
}
export interface AmbienteData {
    id: number;
    codigo?: string;
    nombre: string;
    tipo: string;
    capacidad: number;
    descripcion: string;
    zona: string;
    caracteristicas: string[];
    imagenes: string[];
    mesas_count: number;
    mesas: MesaData[];
}
export interface MenuItemData {
    id: number;
    nombre: string;
    descripcion: string;
    categoria: string;
    categoria_codigo: string;
    precio: number | null;
    moneda: string;
    imagen: string | null;
    etiquetas?: string[];
    tiempo_preparacion?: string;
    disponible?: boolean;
}
