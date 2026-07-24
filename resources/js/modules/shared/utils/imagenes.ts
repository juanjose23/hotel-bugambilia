export const resolverImagenStorage = (
    imagenRaw: string | null | undefined,
    tipoFallback: string = 'general',
    categoria?: string,
): string => {
    if (!imagenRaw) {
        return obtenerImagenFallback(tipoFallback, categoria);
    }

    if (imagenRaw.startsWith('http://') || imagenRaw.startsWith('https://')) {
        return imagenRaw;
    }

    if (imagenRaw.startsWith('/storage/')) {
        return imagenRaw;
    }

    if (imagenRaw.startsWith('images/') || imagenRaw.startsWith('img/')) {
        return `/${imagenRaw}`;
    }

    return `/storage/${imagenRaw}`;
};
const obtenerImagenFallback = (tipo: string, categoria?: string): string => {
    const fallbacks: Record<string, string> = {
        gastronomia: '/images/restaurant.jpg',
        piscina: '/images/pool-scaled.webp',
        bar: '/images/terrace.webp',
        eventos: '/images/event-space.jpg',
        habitacion: '/images/main-room.webp',
        servicio: '/images/service-pool.webp',
        general: '/images/main-room.webp',
    };

    if (categoria && fallbacks[categoria]) {
        return fallbacks[categoria];
    }

    return fallbacks[tipo] || fallbacks.general;
};
