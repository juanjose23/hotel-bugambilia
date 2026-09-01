import {
    Search,
    Users,
    ArrowUpDown,
    RotateCcw,
    SlidersHorizontal,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';

interface HabitacionFiltrosProps {
    categorias: string[];
    categoriaActiva: string;
    alSeleccionarCategoria: (cat: string) => void;
    busqueda: string;
    alCambiarBusqueda: (val: string) => void;
    alBuscar: () => void;
    huespedes: string;
    alCambiarHuespedes: (val: string) => void;
    orden: 'precio_asc' | 'precio_desc' | 'popular';
    alCambiarOrden: (val: 'precio_asc' | 'precio_desc' | 'popular') => void;
    alLimpiar: () => void;
    totalResultados: number;
}

export const HabitacionFiltros = ({
    categorias,
    categoriaActiva,
    alSeleccionarCategoria,
    busqueda,
    alCambiarBusqueda,
    alBuscar,
    huespedes,
    alCambiarHuespedes,
    orden,
    alCambiarOrden,
    alLimpiar,
    totalResultados,
}: HabitacionFiltrosProps) => {
    return (
        <div className="sticky top-16 z-20 border-b border-border bg-card/85 py-3.5 backdrop-blur-xl transition-colors">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    {/* Barra de Categorías (Pills con scroll horizontal táctil) */}
                    <div className="-mx-4 flex scrollbar-none items-center gap-1.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0 sm:pb-0">
                        <Button
                            type="button"
                            variant={
                                categoriaActiva === 'todas'
                                    ? 'default'
                                    : 'secondary'
                            }
                            size="sm"
                            onClick={() => alSeleccionarCategoria('todas')}
                            className={`h-7 shrink-0 rounded-full px-4 text-xs font-black transition-all ${
                                categoriaActiva === 'todas'
                                    ? 'shadow-xs'
                                    : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            Todas las suites
                        </Button>
                        {categorias.map((cat) => {
                            const activo =
                                categoriaActiva.toLowerCase() ===
                                cat.toLowerCase();

                            return (
                                <Button
                                    key={cat}
                                    type="button"
                                    variant={activo ? 'default' : 'secondary'}
                                    size="sm"
                                    onClick={() => alSeleccionarCategoria(cat)}
                                    className={`h-7 shrink-0 rounded-full px-4 text-xs font-black transition-all ${
                                        activo
                                            ? 'shadow-xs'
                                            : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground'
                                    }`}
                                >
                                    {cat}
                                </Button>
                            );
                        })}
                    </div>

                    {/* Controles de Búsqueda y Orden */}
                    <div className="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:gap-3">
                        {/* Buscador Rápido */}
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                alBuscar();
                            }}
                            className="relative w-full sm:w-60"
                        >
                            <Search className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={busqueda}
                                onChange={(e) =>
                                    alCambiarBusqueda(e.target.value)
                                }
                                placeholder="Buscar habitación..."
                                className="h-9 w-full rounded-full bg-background pr-3 pl-9 text-xs font-medium shadow-xs"
                            />
                        </form>

                        {/* Controles secundarios (Huéspedes y Orden) */}
                        <div className="flex w-full items-center gap-2 sm:w-auto">
                            {/* Filtro de Huéspedes */}
                            <div className="flex-1 sm:w-40 sm:flex-initial">
                                <Select
                                    value={huespedes}
                                    onValueChange={alCambiarHuespedes}
                                >
                                    <SelectTrigger className="h-9 w-full rounded-full bg-background text-xs font-bold shadow-xs">
                                        <div className="flex items-center gap-1.5 truncate">
                                            <Users className="size-3.5 text-primary dark:text-rose-400" />
                                            <SelectValue placeholder="Huéspedes" />
                                        </div>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="todos">
                                            Cualquier capacidad
                                        </SelectItem>
                                        <SelectItem value="1">
                                            1 huésped
                                        </SelectItem>
                                        <SelectItem value="2">
                                            2 huéspedes
                                        </SelectItem>
                                        <SelectItem value="3">
                                            3 huéspedes
                                        </SelectItem>
                                        <SelectItem value="4">
                                            4+ huéspedes
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Ordenamiento */}
                            <div className="flex-1 sm:w-40 sm:flex-initial">
                                <Select
                                    value={orden}
                                    onValueChange={(v) =>
                                        alCambiarOrden(
                                            v as
                                                | 'precio_asc'
                                                | 'precio_desc'
                                                | 'popular',
                                        )
                                    }
                                >
                                    <SelectTrigger className="h-9 w-full rounded-full bg-background text-xs font-bold shadow-xs">
                                        <div className="flex items-center gap-1.5 truncate">
                                            <ArrowUpDown className="size-3.5 text-muted-foreground" />
                                            <SelectValue placeholder="Ordenar" />
                                        </div>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="popular">
                                            Más destacadas
                                        </SelectItem>
                                        <SelectItem value="precio_asc">
                                            Precio: menor a mayor
                                        </SelectItem>
                                        <SelectItem value="precio_desc">
                                            Precio: mayor a menor
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Botón de Reset si hay filtros activos */}
                            {(categoriaActiva !== 'todas' ||
                                busqueda !== '' ||
                                huespedes !== 'todos' ||
                                orden !== 'popular') && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onClick={alLimpiar}
                                    className="size-9 shrink-0 cursor-pointer rounded-full text-muted-foreground hover:bg-muted hover:text-foreground"
                                    title="Limpiar filtros"
                                >
                                    <RotateCcw className="size-3.5" />
                                </Button>
                            )}
                        </div>
                    </div>
                </div>

                {/* Resumen de Resultados */}
                <div className="mt-2 flex items-center justify-between text-[11px] font-bold text-muted-foreground">
                    <span>
                        Mostrando{' '}
                        <strong className="text-foreground">
                            {totalResultados}
                        </strong>{' '}
                        {totalResultados === 1 ? 'habitación' : 'habitaciones'}
                    </span>
                    <span className="inline-flex items-center gap-1">
                        <SlidersHorizontal className="size-3" />
                        <span>Impuestos incluidos</span>
                    </span>
                </div>
            </div>
        </div>
    );
};

export default HabitacionFiltros;
