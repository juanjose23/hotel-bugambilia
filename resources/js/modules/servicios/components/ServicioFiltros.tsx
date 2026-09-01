import { Search, X, SlidersHorizontal } from 'lucide-react';
import type { SubmitEvent } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';

interface PropsServicioFiltros {
    categorias: string[];
    categoriaActiva: string;
    alSeleccionarCategoria: (categoria: string) => void;
    busqueda: string;
    alCambiarBusqueda: (busqueda: string) => void;
    alBuscar: (e: SubmitEvent<HTMLFormElement>) => void;
    alLimpiar: () => void;
    totalResultados: number;
}

export const ServicioFiltros = ({
    categorias,
    categoriaActiva,
    alSeleccionarCategoria,
    busqueda,
    alCambiarBusqueda,
    alBuscar,
    alLimpiar,
    totalResultados,
}: PropsServicioFiltros) => {
    const todasLasCategorias = [
        'todas',
        ...(categorias.length > 0
            ? categorias
            : ['Gastronomía', 'Bienestar', 'Bebidas', 'Eventos']),
    ];

    return (
        <div className="sticky top-16 z-20 border-b border-border bg-card/85 py-3.5 backdrop-blur-xl transition-colors">
            <div className="container mx-auto flex flex-col gap-3 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                {/* Selector de Categorías Horizontal */}
                <div className="-mx-4 flex scrollbar-none items-center gap-1.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0 sm:pb-0">
                    {todasLasCategorias.map((cat) => {
                        const activa =
                            categoriaActiva.toLowerCase() === cat.toLowerCase();

                        return (
                            <Button
                                key={cat}
                                type="button"
                                variant={activa ? 'default' : 'secondary'}
                                size="sm"
                                onClick={() => alSeleccionarCategoria(cat)}
                                className={`h-7 shrink-0 rounded-full px-4 text-xs font-black transition-all ${
                                    activa
                                        ? 'shadow-xs'
                                        : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {cat === 'todas' ? 'Todos los Servicios' : cat}
                            </Button>
                        );
                    })}
                </div>

                {/* Barra de Búsqueda y Conteo */}
                <div className="flex items-center gap-3">
                    <form
                        onSubmit={alBuscar}
                        className="relative flex w-full items-center sm:w-64"
                    >
                        <Search className="pointer-events-none absolute left-3 size-3.5 text-muted-foreground" />
                        <Input
                            type="text"
                            value={busqueda}
                            onChange={(e) => alCambiarBusqueda(e.target.value)}
                            placeholder="Buscar servicio..."
                            className="h-9 rounded-full bg-background pr-8 pl-8.5 text-xs font-medium shadow-xs focus-visible:ring-1 focus-visible:ring-primary"
                        />
                        {busqueda && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={alLimpiar}
                                className="absolute right-1 size-6 rounded-full p-0 text-muted-foreground hover:bg-transparent hover:text-foreground"
                            >
                                <X className="size-3.5" />
                            </Button>
                        )}
                    </form>

                    <div className="hidden items-center gap-1.5 text-xs font-bold text-muted-foreground sm:flex">
                        <SlidersHorizontal className="size-3 text-primary dark:text-rose-400" />
                        <span>
                            {totalResultados}{' '}
                            {totalResultados === 1 ? 'servicio' : 'servicios'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ServicioFiltros;
