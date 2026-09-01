import {
    BedDouble,
    Crown,
    Waves,
    Mountain,
    Coffee,
    Award,
    SlidersHorizontal,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';

import type { CategoriaFiltro } from '@/modules/shared/types';

export type CategoriaItem = CategoriaFiltro;

interface PropsCategoryBar {
    categoriaActiva: string;
    alSeleccionarCategoria: (cat: string) => void;
    categorias?: (string | CategoriaFiltro)[];
    alAbrirFiltros?: () => void;
    totalFiltrosActivos?: number;
}

const ICONOS_DEFAULT: Record<string, typeof BedDouble> = {
    todas: BedDouble,
    ejecutiva: Crown,
    suite: Award,
    piscina: Waves,
    panoramica: Mountain,
    desayuno: Coffee,
};

export const CategoryBar = ({
    categoriaActiva,
    alSeleccionarCategoria,
    categorias = [],
    alAbrirFiltros,
    totalFiltrosActivos = 0,
}: PropsCategoryBar) => {
    const listaCategorias = [
        { id: 'todas', label: 'Todas las Habitaciones', icono: BedDouble },
        ...categorias.map((cat) => {
            const nombre = typeof cat === 'string' ? cat : cat.nombre;

            return {
                id: nombre,
                label: nombre,
                icono: ICONOS_DEFAULT[nombre.toLowerCase()] || BedDouble,
            };
        }),
    ];

    return (
        <div className="border-b border-border/60 bg-background/95 pt-6 pb-2 backdrop-blur-md">
            <div className="container mx-auto flex items-center justify-between gap-4 px-4 sm:px-6">
                {/* Categorías Scrolleables */}
                <div className="flex scrollbar-none items-center gap-6 overflow-x-auto pb-1 sm:gap-8">
                    {listaCategorias.map((cat) => {
                        const Icono = cat.icono;
                        const estaActiva =
                            categoriaActiva.toLowerCase() ===
                                cat.id.toLowerCase() ||
                            (cat.id === 'todas' &&
                                (!categoriaActiva ||
                                    categoriaActiva === 'todas'));

                        return (
                            <Button
                                key={cat.id}
                                type="button"
                                variant="ghost"
                                onClick={() => alSeleccionarCategoria(cat.id)}
                                className={`group flex h-auto shrink-0 flex-col items-center gap-1.5 rounded-none border-b-2 bg-transparent px-2 pb-2 hover:bg-transparent ${
                                    estaActiva
                                        ? 'border-foreground text-foreground'
                                        : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'
                                }`}
                            >
                                <Icono
                                    className={`size-5 transition-transform group-hover:scale-110 ${
                                        estaActiva
                                            ? 'text-foreground'
                                            : 'text-muted-foreground'
                                    }`}
                                />
                                <span className="text-xs font-bold whitespace-nowrap">
                                    {cat.label}
                                </span>
                            </Button>
                        );
                    })}
                </div>

                {/* Botón de Filtros Estilo Airbnb */}
                {alAbrirFiltros && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alAbrirFiltros}
                        className="flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2 text-xs font-bold text-foreground shadow-xs transition-all hover:bg-muted active:scale-95"
                    >
                        <SlidersHorizontal className="size-3.5 text-muted-foreground" />
                        <span>Filtros</span>
                        {totalFiltrosActivos > 0 && (
                            <span className="flex size-4 items-center justify-center rounded-full bg-bugambilia-600 text-[10px] font-black text-white">
                                {totalFiltrosActivos}
                            </span>
                        )}
                    </Button>
                )}
            </div>
        </div>
    );
};

export default CategoryBar;
