import {
    Crown,
    Sunrise,
    Sun,
    Moon,
    Wine,
    Coffee,
    Utensils,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { DepartamentoDelivery, MesaData, RestauranteData } from '../types';

export const OCASIONES_RAPIDAS = [
    '🎂 Cumpleaños',
    '💍 Aniversario',
    '🌿 Preferencia Terraza',
    '👶 Silla para Bebé',
    '💼 Almuerzo de Negocios',
];

export const OPCIONES_COMENSALES = [1, 2, 3, 4, 5, 6, 8, 10];

export interface TurnoRestaurante {
    id: 'todos' | 'Desayuno' | 'Almuerzo' | 'Cena';
    label: string;
    icono: LucideIcon;
}

export const TURNOS_RESTAURANTE: TurnoRestaurante[] = [
    { id: 'todos', label: 'Todos', icono: Utensils },
    { id: 'Desayuno', label: 'Desayuno', icono: Sunrise },
    { id: 'Almuerzo', label: 'Almuerzo', icono: Sun },
    { id: 'Cena', label: 'Cena', icono: Moon },
];

export const iconoTurnoRapido = (turno: string): LucideIcon | null => {
    switch (turno) {
        case 'Desayuno':
            return Sunrise;
        case 'Almuerzo':
            return Sun;
        case 'Cena':
            return Moon;
        default:
            return null;
    }
};

export interface ZonaRestauranteConfig {
    id: string;
    nombre: string;
    descripcion: string;
    icono: LucideIcon;
    colorBorde: string;
    colorFondo: string;
    colorBadge: string;
    colorTitulo: string;
}

export const ZONAS_RESTAURANTE: Record<string, ZonaRestauranteConfig> = {
    interior: {
        id: 'interior',
        nombre: 'Salón Principal Interior',
        descripcion: 'Ambiente climatizado, iluminación cálida y música suave.',
        icono: Utensils,
        colorBorde: 'border-emerald-500/30',
        colorFondo: 'bg-emerald-500/5',
        colorBadge:
            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
        colorTitulo: 'text-emerald-600 dark:text-emerald-400',
    },
    terraza: {
        id: 'terraza',
        nombre: 'Terraza al Aire Libre',
        descripcion: 'Rodeada de bugambilias y jardines con brisa fresca.',
        icono: Coffee,
        colorBorde: 'border-amber-500/30',
        colorFondo: 'bg-amber-500/5',
        colorBadge:
            'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        colorTitulo: 'text-amber-600 dark:text-amber-400',
    },
    barra: {
        id: 'barra',
        nombre: 'Bar & Lounge El Mirador',
        descripcion: 'Coctelería de autor, vinos finos y atmósfera moderna.',
        icono: Wine,
        colorBorde: 'border-indigo-500/30',
        colorFondo: 'bg-indigo-500/5',
        colorBadge:
            'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
        colorTitulo: 'text-indigo-600 dark:text-indigo-400',
    },
    vip: {
        id: 'vip',
        nombre: 'Cenador Privado VIP',
        descripcion:
            'Espacio exclusivo con atención personalizada y privacidad.',
        icono: Crown,
        colorBorde: 'border-purple-500/30',
        colorFondo: 'bg-purple-500/5',
        colorBadge:
            'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
        colorTitulo: 'text-purple-600 dark:text-purple-400',
    },
};

export type ClaveZonaRestaurante = 'interior' | 'terraza' | 'barra' | 'vip';

export const normalizarClaveZona = (zona?: string): ClaveZonaRestaurante => {
    const clean = (zona || 'interior').toLowerCase().trim();

    if (clean === 'bar' || clean === 'barra' || clean.includes('bar')) {
        return 'barra';
    }

    if (clean === 'terraza' || clean.includes('terra')) {
        return 'terraza';
    }

    if (clean === 'vip' || clean.includes('vip')) {
        return 'vip';
    }

    return 'interior';
};

export const mesasPorDefecto: MesaData[] = [
    {
        id: 1,
        nombre: 'Mesa 01',
        capacidad: 2,
        tipo_mesa: 'redonda',
        zona: 'interior',
        orden: 1,
    },
    {
        id: 2,
        nombre: 'Mesa 02',
        capacidad: 2,
        tipo_mesa: 'cuadrada',
        zona: 'interior',
        orden: 2,
    },
    {
        id: 3,
        nombre: 'Mesa 03',
        capacidad: 4,
        tipo_mesa: 'cuadrada',
        zona: 'interior',
        orden: 3,
    },
    {
        id: 4,
        nombre: 'Mesa 04',
        capacidad: 4,
        tipo_mesa: 'cuadrada',
        zona: 'interior',
        orden: 4,
    },
    {
        id: 5,
        nombre: 'Mesa 05',
        capacidad: 6,
        tipo_mesa: 'rectangular',
        zona: 'interior',
        orden: 5,
    },
    {
        id: 6,
        nombre: 'Mesa 06',
        capacidad: 4,
        tipo_mesa: 'cuadrada',
        zona: 'terraza',
        orden: 6,
    },
    {
        id: 7,
        nombre: 'Mesa 07 (VIP)',
        capacidad: 4,
        tipo_mesa: 'redonda',
        zona: 'terraza',
        orden: 7,
    },
    {
        id: 8,
        nombre: 'Mesa 08',
        capacidad: 2,
        tipo_mesa: 'redonda',
        zona: 'terraza',
        orden: 8,
    },
    {
        id: 9,
        nombre: 'Barra 01',
        capacidad: 1,
        tipo_mesa: 'barra',
        zona: 'bar',
        orden: 9,
    },
    {
        id: 10,
        nombre: 'Barra 02',
        capacidad: 1,
        tipo_mesa: 'barra',
        zona: 'bar',
        orden: 10,
    },
];

export const departamentosPorDefecto: DepartamentoDelivery[] = [
    {
        codigo: 'EST',
        nombre: 'Estelí',
        activo: true,
        costo_envio: 50.0,
        municipios: [
            'Estelí',
            'La Trinidad',
            'Condega',
            'Pueblo Nuevo',
            'San Juan de Limay',
            'San Nicolás',
        ],
    },
];

export const crearRestaurantePorDefecto = (
    departamentos_delivery: DepartamentoDelivery[] = departamentosPorDefecto,
): RestauranteData => ({
    id: 1,
    nombre: 'Restaurante Bugambilias',
    descripcion:
        'Una experiencia gastronómica de alta cocina en Estelí, combinando ingredientes frescos en ambientes acogedores y exclusivos.',
    capacidad: 40,
    imagenes: [
        '/images/service-events.webp',
        '/images/service-bartender.webp',
        '/images/terrace.webp',
    ],
    tipo_cocina: 'Nicaragüense Gourmet & Internacional',
    tipo_servicio: 'A la carta / Menú Ejecutivo',
    horario_desayuno: '07:00 - 10:30 AM',
    horario_almuerzo: '12:00 - 03:30 PM',
    horario_cena: '06:00 - 10:00 PM',
    whatsapp_reservas: '+50588888888',
    telefono_reservas: '+505 8713 6805',
    permite_delivery: true,
    costo_delivery: 50,
    pedido_minimo: 0,
    departamentos_delivery,
});
