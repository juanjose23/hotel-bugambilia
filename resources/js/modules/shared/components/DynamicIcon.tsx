import type { LucideProps } from 'lucide-react';
import {
    ArrowUpCircle,
    Bath,
    Bed,
    Bike,
    Briefcase,
    Building2,
    Cake,
    CalendarDays,
    Car,
    Clock,
    Clock3,
    Coffee,
    ConciergeBell,
    CreditCard,
    Flame,
    Gift,
    Heart,
    Laptop,
    LogOut,
    Map,
    MapPin,
    Plane,
    Presentation,
    Scissors,
    Shirt,
    Sparkles,
    Star,
    Sun,
    Tag,
    Utensils,
    Wifi,
    Wine,
} from 'lucide-react';
import React from 'react';

const ICON_MAP: Record<string, React.ComponentType<LucideProps>> = {
    ArrowUpCircle,
    Bath,
    Bed,
    Bike,
    Briefcase,
    Building2,
    Cake,
    CalendarDays,
    Car,
    Clock,
    Clock3,
    Coffee,
    ConciergeBell,
    CreditCard,
    Flame,
    Gift,
    Heart,
    Laptop,
    LogOut,
    Map,
    MapPin,
    Plane,
    Presentation,
    Scissors,
    Shirt,
    Sparkles,
    Star,
    Sun,
    Tag,
    Utensils,
    Wifi,
    Wine,
};

export interface DynamicIconProps extends Omit<LucideProps, 'name'> {
    name?: string | null;
    fallback?: string;
}

/**
 * Renderiza de forma dinámica iconos de Lucide React a partir de su nombre (kebab-case, camelCase o PascalCase).
 * Optimizado para importar únicamente los iconos usados por el dominio del hotel evitando cargar 1500+ iconos innecesarios.
 */
export const DynamicIcon = ({
    name,
    fallback = 'Sparkles',
    ...props
}: DynamicIconProps) => {
    const FallbackComponent = ICON_MAP[fallback] ?? Sparkles;

    if (!name || typeof name !== 'string') {
        return <FallbackComponent {...props} />;
    }

    // Limpiar prefijos de heroicon o blade si existieran
    const clean = name.replace(/^heroicon-[osml]-/, '');

    // Convertir kebab-case o snake_case a PascalCase (ej. "concierge-bell" -> "ConciergeBell", "wifi" -> "Wifi")
    const pascalName = clean
        .split(/[-_]/)
        .map(
            (segment) =>
                segment.charAt(0).toUpperCase() +
                segment.slice(1).toLowerCase(),
        )
        .join('');

    const IconComponent = ICON_MAP[pascalName] ?? FallbackComponent;

    return <IconComponent {...props} />;
};

export default DynamicIcon;
