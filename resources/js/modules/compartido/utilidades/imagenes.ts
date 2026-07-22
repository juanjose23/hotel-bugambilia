export function resolverImagenStorage(
    imagenRaw?: string | null,
    tipoFallback: 'servicio' | 'habitaciones' | 'general' = 'general',
    categoria?: string,
): string {
    if (imagenRaw && imagenRaw.trim() !== '') {
        const img = imagenRaw.trim();

        if (img.startsWith('http://') || img.startsWith('https://')) {
            return img;
        }

        if (img.startsWith('/')) {
            return img;
        }

        if (img.startsWith('storage/')) {
            return `/${img}`;
        }

        return `/storage/${img}`;
    }

    const cat = (categoria || '').toLowerCase();

    if (tipoFallback === 'servicio') {
        if (
            cat.includes('gastro') ||
            cat.includes('restaurante') ||
            cat.includes('comida')
        ) {
            return '/images/service-kitchen.png';
        }

        if (cat.includes('piscina') || cat.includes('solárium')) {
            return '/images/service-pool.png';
        }

        if (
            cat.includes('bar') ||
            cat.includes('cóctel') ||
            cat.includes('bebida')
        ) {
            return '/images/service-bartender.png';
        }

        if (cat.includes('evento') || cat.includes('reunión')) {
            return '/images/service-events.png';
        }

        return '/images/terrace.jpg';
    }

    if (tipoFallback === 'habitaciones') {
        return '/images/main-room.jpg';
    }

    return '/images/terrace.jpg';
}
