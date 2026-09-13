export interface DepartamentoDelivery {
    codigo: string;
    nombre: string;
    activo: boolean;
    costo_envio: number;
    municipios: string[];
}

export interface RestauranteData {
    id: number;
    nombre: string;
    descripcion: string;
    capacidad: number;
    imagenes: string[];
    tipo_cocina: string;
    tipo_servicio: string;
    horario_desayuno: string;
    horario_almuerzo: string;
    horario_cena: string;
    whatsapp_reservas?: string;
    telefono_reservas?: string;
    permite_delivery?: boolean;
    costo_delivery?: number;
    pedido_minimo?: number;
    departamentos_delivery?: DepartamentoDelivery[];
}

export interface MesaData {
    id: number;
    nombre: string;
    capacidad: number;
    tipo_mesa: string;
    zona: string;
    orden?: number;
    ubicacion?: string;
}

export interface AmbienteData {
    id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    capacidad: number;
    descripcion: string;
    zona: string;
    caracteristicas: string[];
    imagenes: string[];
    mesas_count: number;
    mesas?: MesaData[];
}

export interface MenuItemData {
    id: number;
    nombre: string;
    descripcion: string;
    categoria: string;
    categoria_codigo: string;
    precio: number | null;
    moneda: string;
    imagen: string;
    etiquetas: string[];
    tiempo_preparacion: string;
    disponible: boolean;
}

export interface ItemCarritoRestaurante {
    plato: MenuItemData;
    cantidad: number;
    notas?: string;
}

export interface DatosEnvioPedido {
    nombre: string;
    telefono: string;
    departamento: string;
    municipio: string;
    direccion: string;
    metodoPago: 'efectivo' | 'stripe';
    montoPagaCon?: string;
    notas?: string;
}

export interface RestaurantePageProps {
    restaurante?: RestauranteData | null;
    ambientes?: AmbienteData[];
    mesas?: MesaData[];
    menu?: MenuItemData[];
    departamentos_delivery?: DepartamentoDelivery[];
}

export interface RestauranteCheckoutPageProps {
    restaurante?: RestauranteData | null;
    menu?: MenuItemData[];
    ambientes?: AmbienteData[];
    mesas?: MesaData[];
    departamentos_delivery?: DepartamentoDelivery[];
}
