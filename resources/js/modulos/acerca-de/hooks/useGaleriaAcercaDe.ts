import { useState } from 'react';
import {
    ELEMENTOS_GALERIA,
    CATEGORIAS_GALERIA,
} from '../constantes/acercaDeConstantes';
import type { ElementoGaleria } from '../interfaces/acercaDeInterfaces';

export function useGaleriaAcercaDe(
    itemsIniciales: ElementoGaleria[] = ELEMENTOS_GALERIA,
) {
    const [activeCategory, setActiveCategory] = useState<string>('Todas');
    const [activeImageIndex, setActiveImageIndex] = useState<number | null>(
        null,
    );

    const itemsFiltrados =
        activeCategory === 'Todas'
            ? itemsIniciales
            : itemsIniciales.filter(
                  (item) =>
                      (item.categoria || item.category) === activeCategory,
              );

    const imagenesUrls: string[] = itemsFiltrados
        .map((item) => item.imagen || item.src || '')
        .filter((url): url is string => Boolean(url));

    const abrirVisor = (index: number) => {
        setActiveImageIndex(index);
    };

    const cerrarVisor = () => {
        setActiveImageIndex(null);
    };

    return {
        categorias: CATEGORIAS_GALERIA,
        categoriaActiva: activeCategory,
        setCategoriaActiva: setActiveCategory,
        itemsFiltrados,
        imagenesUrls,
        indiceImagenActiva: activeImageIndex,
        abrirVisor,
        cerrarVisor,
    };
}
