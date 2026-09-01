import { Search, X, SlidersHorizontal, RotateCcw } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';

import type { TipoEspacioOpcion } from '../types';

interface PropsEspacioFiltros {
    tipos: TipoEspacioOpcion[];
    tipoActivo: string;
    alSeleccionarTipo: (tipo: string) => void;
    busqueda: string;
    alCambiarBusqueda: (valor: string) => void;
    capacidadMinima: number;
    alCambiarCapacidad: (valor: number) => void;
    alLimpiar: () => void;
    totalResultados: number;
}

export const EspacioFiltros = ({
    tipos,
    tipoActivo,
    alSeleccionarTipo,
    busqueda,
    alCambiarBusqueda,
    alLimpiar,
    totalResultados,
}: PropsEspacioFiltros) => {
    const tiposDisponibles = [
        { tipo: 'TODOS', label: 'Todos los Espacios' },
        ...tipos,
    ];

    return (
        <div className="sticky top-16 z-20 border-b border-border bg-card/85 py-3.5 backdrop-blur-xl transition-colors">
            <div className="container mx-auto flex flex-col gap-3 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                {/* Selector Horizontal de Tipos */}
                <div className="-mx-4 flex scrollbar-none items-center gap-1.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0 sm:pb-0">
                    {tiposDisponibles.map((item) => {
                        const activo =
                            tipoActivo.toUpperCase() ===
                            item.tipo.toUpperCase();

                        return (
                            <Button
                                key={item.tipo}
                                type="button"
                                variant={activo ? 'default' : 'secondary'}
                                size="sm"
                                onClick={() => alSeleccionarTipo(item.tipo)}
                                className={`h-7 shrink-0 rounded-full px-4 text-xs font-black transition-all ${
                                    activo
                                        ? 'shadow-xs'
                                        : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {item.label}
                            </Button>
                        );
                    })}
                </div>

                {/* Buscador y Resumen */}
                <div className="flex items-center gap-3">
                    <div className="relative flex w-full items-center sm:w-64">
                        <Search className="pointer-events-none absolute left-3 size-3.5 text-muted-foreground" />
                        <Input
                            type="text"
                            value={busqueda}
                            onChange={(e) => alCambiarBusqueda(e.target.value)}
                            placeholder="Buscar salón o área..."
                            className="h-9 rounded-full bg-background pr-8 pl-8.5 text-xs font-medium shadow-xs focus-visible:ring-1 focus-visible:ring-primary"
                        />
                        {busqueda && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() => alCambiarBusqueda('')}
                                className="absolute right-1 size-6 rounded-full p-0 text-muted-foreground hover:bg-transparent hover:text-foreground"
                            >
                                <X className="size-3.5" />
                            </Button>
                        )}
                    </div>

                    {(tipoActivo !== 'TODOS' || busqueda !== '') && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={alLimpiar}
                            className="h-9 cursor-pointer rounded-full px-2.5 text-xs font-bold text-muted-foreground hover:text-foreground"
                        >
                            <RotateCcw className="size-3.5" />
                            <span className="hidden sm:inline">Limpiar</span>
                        </Button>
                    )}

                    <div className="hidden items-center gap-1.5 text-xs font-bold text-muted-foreground sm:flex">
                        <SlidersHorizontal className="size-3 text-primary dark:text-rose-400" />
                        <span>
                            {totalResultados}{' '}
                            {totalResultados === 1 ? 'espacio' : 'espacios'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EspacioFiltros;
