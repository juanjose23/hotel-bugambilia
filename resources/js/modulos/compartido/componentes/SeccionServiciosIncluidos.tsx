import * as LucideIcons from 'lucide-react';
import {
    Wifi,
    Wind,
    Tv,
    Bath,
    Coffee,
    Waves,
    Utensils,
    Car,
    ShieldCheck,
    CheckCircle2,
    Dumbbell,
    Wine,
    ConciergeBell,
    Clock,
    Lock,
    KeyRound,
    Briefcase,
    Sun,
    Flame,
    MapPin,
    Globe,
    Heart,
    Gift,
    Trophy,
    Star,
    Scissors,
    Users,
    FileText,
    LogOut,
    Wrench,
    Home,
    ShoppingBag,
    Presentation,
    Hand,
    Plane,
    Laptop,
    BadgeCheck,
    BedDouble,
    Shirt,
    PhoneCall,
    Martini,
    Calendar,
    CreditCard,
    Percent,
} from 'lucide-react';
import React from 'react';

export interface ItemServicioData {
    nombre: string;
    descripcion?: string | null;
    icono?: string | null;
    incluido?: boolean | null;
}

interface PropiedadesSeccionServiciosIncluidos {
    servicios?: Array<ItemServicioData | string>;
    titulo?: string;
    subtitulo?: string;
    className?: string;
}

// Mapa exhaustivo de iconos guardados en la BD (Heroicons y Lucide convertidos a Lucide React)
const MAPA_ICONOS: Record<string, React.ElementType> = {
    // Convenciones Heroicons de Filament / Seeders
    'heroicon-o-wifi': Wifi,
    'heroicon-o-clock': Clock,
    'heroicon-o-arrow-right-on-rectangle': LogOut,
    'heroicon-o-home-modern': Home,
    'heroicon-o-fire': Flame,
    'heroicon-o-sun': Sun,
    'heroicon-o-key': KeyRound,
    'heroicon-o-briefcase': Briefcase,
    'heroicon-o-shopping-bag': ShoppingBag,
    'heroicon-o-scissors': Scissors,
    'heroicon-o-BadgeCheck': BadgeCheck,
    'heroicon-o-users': Users,
    'heroicon-o-document-text': FileText,
    'heroicon-o-presentation-chart-line': Presentation,
    'heroicon-o-map': MapPin,
    'heroicon-o-globe-alt': Globe,
    'heroicon-o-heart': Heart,
    'heroicon-o-gift': Gift,
    'heroicon-o-trophy': Trophy,
    'heroicon-o-star': Star,
    'heroicon-o-computer-desktop': Tv,
    'heroicon-o-wrench-screwdriver': Wrench,
    'heroicon-o-hand-raised': Hand,
    'heroicon-o-paper-airplane': Plane,

    // Nombres cortos Lucide / BD
    wifi: Wifi,
    BadgeCheck: BadgeCheck,
    clock: Clock,
    key: KeyRound,
    briefcase: Briefcase,
    sun: Sun,
    flame: Flame,
    bath: Bath,
    coffee: Coffee,
    lock: Lock,
    pool: Waves,
    swimming: Waves,
    utensils: Utensils,
    restaurant: Utensils,
    bar: Martini,
    cocktail: Martini,
    car: Car,
    parking: Car,
    laundry: Shirt,
    shirt: Shirt,
    phone: PhoneCall,
    gym: Dumbbell,
    fitness: Dumbbell,
    wine: Wine,
    concierge: ConciergeBell,
    bell: ConciergeBell,
    laptop: Laptop,
    desktop: Tv,
    tv: Tv,
    ac: Wind,
    wind: Wind,
    bed: BedDouble,
    calendar: Calendar,
    card: CreditCard,
    percent: Percent,
    gift: Gift,
    shield: ShieldCheck,
};

const CATEGORIAS_COLORES = [
    'from-amber-500/10 via-bugambilia-500/10 to-rose-500/10 text-amber-500 border-amber-500/20',
    'from-sky-500/10 via-cyan-500/10 to-blue-500/10 text-sky-500 border-sky-500/20',
    'from-emerald-500/10 via-teal-500/10 to-emerald-600/10 text-emerald-500 border-emerald-500/20',
    'from-purple-500/10 via-violet-500/10 to-indigo-500/10 text-purple-500 border-purple-500/20',
    'from-rose-500/10 via-bugambilia-500/10 to-pink-500/10 text-bugambilia-500 border-bugambilia-500/20',
];

function resolverIconoBD(iconoBD?: string | null): React.ElementType {
    if (!iconoBD) {
        return CheckCircle2;
    }

    const claveLimpia = iconoBD.toLowerCase().trim();

    if (MAPA_ICONOS[claveLimpia]) {
        return MAPA_ICONOS[claveLimpia];
    }

    // Intentar remoción de prefijos comunes heroicon-o-, heroicon-s-, heroicon-m-, lucide-
    const claveSinPrefijo = claveLimpia
        .replace(/^heroicon-[osm]-/, '')
        .replace(/^lucide-/, '');

    if (MAPA_ICONOS[claveSinPrefijo]) {
        return MAPA_ICONOS[claveSinPrefijo];
    }

    // Convertir kebab-case a PascalCase para resolver cualquier icono de node_modules/lucide-react
    const pascalName = claveSinPrefijo
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');

    const DynamicIcon = (
        LucideIcons as unknown as Record<string, React.ElementType>
    )[pascalName];

    if (DynamicIcon) {
        return DynamicIcon;
    }

    // Si no se encuentra icono específico, usar CheckCircle2 como icono neutro y elegante
    return CheckCircle2;
}

export const SeccionServiciosIncluidos: React.FC<
    PropiedadesSeccionServiciosIncluidos
> = ({
    servicios = [],
    titulo = 'Experiencias & Amenidades',
    subtitulo = 'Servicios e instalaciones exclusivas confirmadas para su disfrute',
    className = '',
}) => {
    // Si no existen servicios asignados en la BD, NO se renderiza la sección
    if (!servicios || servicios.length === 0) {
        return null;
    }

    const itemsNormalizados: ItemServicioData[] = servicios.map((s) =>
        typeof s === 'string' ? { nombre: s } : s,
    );

    return (
        <section
            className={`border-t border-border/40 bg-card/30 py-12 font-sans md:py-16 ${className}`}
        >
            <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <header className="mb-10">
                    <div className="inline-flex items-center gap-2 rounded-full border border-bugambilia-500/30 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                        <ShieldCheck className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                        <span>Amenidades Garantizadas</span>
                    </div>
                    <h2 className="mt-3 text-2xl font-black tracking-tight text-foreground md:text-3xl">
                        {titulo}{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Incluidas
                        </span>
                    </h2>
                    {subtitulo && (
                        <p className="mt-1 text-sm font-medium text-muted-foreground">
                            {subtitulo}
                        </p>
                    )}
                </header>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {itemsNormalizados.map((item, index) => {
                        const IconoComponente = resolverIconoBD(item.icono);
                        const estiloGradiente =
                            CATEGORIAS_COLORES[
                                index % CATEGORIAS_COLORES.length
                            ];

                        return (
                            <div
                                key={index}
                                className="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-background/90 p-5 shadow-xs transition-all duration-500 hover:-translate-y-1.5 hover:border-bugambilia-500/50 hover:shadow-xl dark:bg-card/90"
                            >
                                <div className="absolute top-0 right-0 -mt-6 -mr-6 h-20 w-20 rounded-full bg-bugambilia-500/5 transition-transform duration-500 group-hover:scale-150" />

                                <div>
                                    <div className="mb-4 flex items-center justify-between">
                                        <div
                                            className={`flex h-12 w-12 items-center justify-center rounded-2xl border bg-gradient-to-br transition-all duration-500 group-hover:scale-110 group-hover:shadow-md ${estiloGradiente}`}
                                        >
                                            <IconoComponente className="h-6 w-6 stroke-[1.8]" />
                                        </div>
                                        {item.incluido === true && (
                                            <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <span className="relative flex h-1.5 w-1.5">
                                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                                    <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                                </span>
                                                Incluido
                                            </span>
                                        )}
                                        {item.incluido === false && (
                                            <span className="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                                Costo Adicional
                                            </span>
                                        )}
                                    </div>

                                    <h3 className="text-sm font-black tracking-tight text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                        {item.nombre}
                                    </h3>

                                    {item.descripcion && (
                                        <p className="mt-2 text-xs leading-relaxed font-medium text-muted-foreground">
                                            {item.descripcion}
                                        </p>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
};
