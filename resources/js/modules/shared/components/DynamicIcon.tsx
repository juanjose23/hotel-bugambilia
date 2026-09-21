import type { LucideProps } from 'lucide-react';
import { icons, Sparkles } from 'lucide-react';
import React from 'react';

export interface DynamicIconProps extends Omit<LucideProps, 'name'> {
    name?: string | null;
    fallback?: keyof typeof icons;
}

/**
 * Renderiza de forma dinámica cualquier icono de Lucide React a partir de su nombre (kebab-case, camelCase o PascalCase).
 * Compatible con los nombres provistos por node_modules/lucide-react e importados desde la base de datos.
 */
export const DynamicIcon = ({
    name,
    fallback = 'Sparkles',
    ...props
}: DynamicIconProps) => {
    if (!name || typeof name !== 'string') {
        const FallbackComponent = icons[fallback] ?? Sparkles;

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
        .join('') as keyof typeof icons;

    const IconComponent = icons[pascalName] ?? icons[fallback] ?? Sparkles;

    return <IconComponent {...props} />;
};

export default DynamicIcon;
