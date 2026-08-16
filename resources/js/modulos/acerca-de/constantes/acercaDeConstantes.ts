import type {
    ElementoGaleria,
    ItemValorHotel,
    HitoHistoria,
} from '../interfaces/acercaDeInterfaces';

export const CATEGORIAS_GALERIA = [
    'Todas',
    'Habitaciones & Suites',
    'Piscina & Terraza',
    'Gastronomía',
    'Instalaciones',
] as const;

export const ELEMENTOS_GALERIA: ElementoGaleria[] = [
    {
        id: 1,
        src: '/images/hero-main.webp',
        alt: 'Vista panorámica del hotel',
        title: 'Fachada & Entrada Principal',
        category: 'Instalaciones',
    },
    {
        id: 2,
        src: '/images/pool-front-view.webp',
        alt: 'Piscina y solárium al atardecer',
        title: 'Piscina & Solárium Tropical',
        category: 'Piscina & Terraza',
    },
    {
        id: 3,
        src: '/images/main-room.webp',
        alt: 'Habitación Matrimonial King',
        title: 'Habitación Matrimonial King',
        category: 'Habitaciones & Suites',
    },
    {
        id: 4,
        src: '/images/terrace.webp',
        alt: 'Terraza Lounge y jardines',
        title: 'Terraza Lounge al Aire Libre',
        category: 'Piscina & Terraza',
    },
    {
        id: 5,
        src: '/images/service-kitchen.webp',
        alt: 'Restaurante gourmet nicaragüense',
        title: 'Restaurante & Experiencia Culinaria',
        category: 'Gastronomía',
    },
    {
        id: 6,
        src: '/images/group-room.webp',
        alt: 'Suite Familiar de lujo',
        title: 'Suite Doble Familiar',
        category: 'Habitaciones & Suites',
    },
    {
        id: 7,
        src: '/images/service-pool.webp',
        alt: 'Área de relajación en la piscina',
        title: 'Área de Descanso Junto al Agua',
        category: 'Piscina & Terraza',
    },
    {
        id: 8,
        src: '/images/bathroom.webp',
        alt: 'Baño privado de mármol',
        title: 'Baño Privado & Acabados de Lujo',
        category: 'Habitaciones & Suites',
    },
    {
        id: 9,
        src: '/images/service-bartender.webp',
        alt: 'Bar y coctelería artesanal',
        title: 'Bar & Coctelería Artesanal',
        category: 'Gastronomía',
    },
];

export const VALORES_HOTEL: ItemValorHotel[] = [
    {
        id: 1,
        titulo: 'Hospitalidad Cálida',
        descripcion:
            'Atención personalizada y familiar que hace sentir a cada huésped como en casa.',
        icono: 'HeartHandshake',
    },
    {
        id: 2,
        titulo: 'Excelencia y Calidad',
        descripcion:
            'Compromiso riguroso en cada detalle de nuestras habitaciones, gastronomía y servicios.',
        icono: 'Award',
    },
    {
        id: 3,
        titulo: 'Sostenibilidad Ambiental',
        descripcion:
            'Uso responsable de recursos, energía solar y programas de residuo cero.',
        icono: 'Leaf',
    },
    {
        id: 4,
        titulo: 'Identidad Local',
        descripcion:
            'Promoción de la cultura, artesanía y gastronomía nicaragüense.',
        icono: 'Compass',
    },
];

export const HITOS_HISTORIA: HitoHistoria[] = [
    {
        ano: '2008',
        titulo: 'Fundación del Hotel',
        descripcion:
            'Abrió sus puertas con 8 habitaciones boutique en el centro de Estelí.',
    },
    {
        ano: '2014',
        titulo: 'Expansión de Restaurante & Piscina',
        descripcion:
            'Inauguración del área de piscina tropical y el restaurante gurmet.',
    },
    {
        ano: '2020',
        titulo: 'Transformación Ecológica',
        descripcion:
            'Implementación de paneles solares y jardines de ventilación natural.',
    },
    {
        ano: '2026',
        titulo: 'Renovación de Suites Boutique',
        descripcion:
            'Lanzamiento de las nuevas Suites Premium con tecnología inteligente y confort superior.',
    },
];
