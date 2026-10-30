import {
    Search,
    Users,
    ArrowUpDown,
    RotateCcw,
    Calendar,
    CalendarCheck,
    CalendarDays,
    SlidersHorizontal,
    Sparkles,
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
    checkIn?: string;
    alCambiarCheckIn?: (val: string) => void;
    checkOut?: string;
    alCambiarCheckOut?: (val: string) => void;
    noches?: number;
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
    checkIn = '',
    alCambiarCheckIn,
    checkOut = '',
    alCambiarCheckOut,
    noches = 0,
    orden,
    alCambiarOrden,
    alLimpiar,
    totalResultados,
}: HabitacionFiltrosProps) => {
    const hoy = new Date().toISOString().split('T')[0];

    const formatearFechaDisplay = (str?: string) => {
        if (!str) {
            return '';
        }

        try {
            const [, m, d] = str.split('-');
            const meses = [
                'ene',
                'feb',
                'mar',
                'abr',
                'may',
                'jun',
                'jul',
                'ago',
                'sep',
                'oct',
                'nov',
                'dic',
            ];
            const numMes = parseInt(m, 10) - 1;

            return `${parseInt(d, 10)} ${meses[numMes] || ''}`;
        } catch {
            return str;
        }
    };

    const textoResumenFechas =
        checkIn && checkOut && noches > 0
            ? `${formatearFechaDisplay(checkIn)} — ${formatearFechaDisplay(checkOut)} · ${noches} ${
                  noches === 1 ? 'noche' : 'noches'
              } · ${huespedes === 'todos' ? '2 huéspedes' : `${huespedes} personas`}`
            : null;

    return (
        <div className="sticky top-16 z-20 border-b border-border bg-card/90 py-4 backdrop-blur-xl transition-colors">
            <div className="container mx-auto space-y-4 px-4 sm:px-6">
                {/* 1. Módulo Principal "¿Cuándo te hospedas?" */}
                <div className="rounded-3xl border border-border/80 bg-background/80 p-3 shadow-md sm:p-4">
                    <div className="mb-2.5 flex items-center justify-between px-1">
                        <div className="flex items-center gap-2">
                            <Calendar className="size-4 text-primary dark:text-rose-400" />
                            <span className="text-xs font-black tracking-tight text-foreground uppercase">
                                ¿Cuándo te hospedas?
                            </span>
                        </div>
                        {textoResumenFechas && (
                            <span className="text-xs font-bold text-primary dark:text-rose-300">
                                {textoResumenFechas}
                            </span>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-12">
                        {/* Entrada */}
                        <div className="lg:col-span-3">
                            <div className="relative rounded-2xl border border-border/80 bg-card p-2.5 transition-colors focus-within:border-primary/50">
                                <label className="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                    <CalendarDays className="size-3 text-primary dark:text-rose-400" />
                                    <span>Entrada</span>
                                </label>
                                <Input
                                    type="date"
                                    min={hoy}
                                    value={checkIn}
                                    onChange={(e) =>
                                        alCambiarCheckIn?.(e.target.value)
                                    }
                                    className="mt-0.5 h-8 border-none bg-transparent p-0 text-xs font-bold text-foreground focus-visible:ring-0"
                                />
                            </div>
                        </div>

                        {/* Salida */}
                        <div className="lg:col-span-3">
                            <div className="relative rounded-2xl border border-border/80 bg-card p-2.5 transition-colors focus-within:border-primary/50">
                                <label className="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                    <CalendarCheck className="size-3 text-primary dark:text-rose-400" />
                                    <span>Salida</span>
                                </label>
                                <Input
                                    type="date"
                                    min={checkIn || hoy}
                                    value={checkOut}
                                    onChange={(e) =>
                                        alCambiarCheckOut?.(e.target.value)
                                    }
                                    className="mt-0.5 h-8 border-none bg-transparent p-0 text-xs font-bold text-foreground focus-visible:ring-0"
                                />
                            </div>
                        </div>

                        {/* Huéspedes */}
                        <div className="lg:col-span-3">
                            <div className="rounded-2xl border border-border/80 bg-card p-2.5 transition-colors focus-within:border-primary/50">
                                <label className="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                    <Users className="size-3 text-primary dark:text-rose-400" />
                                    <span>Huéspedes</span>
                                </label>
                                <Select
                                    value={huespedes}
                                    onValueChange={alCambiarHuespedes}
                                >
                                    <SelectTrigger className="mt-0.5 h-8 border-none bg-transparent p-0 text-xs font-bold text-foreground shadow-none focus-visible:ring-0">
                                        <SelectValue placeholder="Capacidad" />
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
                        </div>

                        {/* Botón CTA: Ver habitaciones */}
                        <div className="flex items-center sm:col-span-2 lg:col-span-3">
                            <Button
                                type="button"
                                onClick={alBuscar}
                                className="h-full min-h-[50px] w-full rounded-2xl bg-primary px-4 text-xs font-black text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95"
                            >
                                <Sparkles className="mr-1.5 size-4" />
                                <span>Ver habitaciones</span>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* 2. Barra secundaria de Categorías, Búsqueda y Ordenamiento */}
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    {/* Pills de categorías */}
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
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                alBuscar();
                            }}
                            className="relative w-full sm:w-56"
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

                        <div className="flex w-full items-center gap-2 sm:w-auto">
                            <div className="w-36">
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

                            {(categoriaActiva !== 'todas' ||
                                busqueda !== '' ||
                                huespedes !== 'todos' ||
                                checkIn !== '' ||
                                checkOut !== '' ||
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

                {/* Subtítulo de resultados */}
                <div className="flex items-center justify-between text-[11px] font-bold text-muted-foreground">
                    <span>
                        Mostrando{' '}
                        <strong className="text-foreground">
                            {totalResultados}
                        </strong>{' '}
                        {totalResultados === 1
                            ? 'habitación disponible'
                            : 'habitaciones disponibles'}
                    </span>
                    <span className="inline-flex items-center gap-1">
                        <SlidersHorizontal className="size-3" />
                        <span>Impuestos incluidos • Cancelación flexible</span>
                    </span>
                </div>
            </div>
        </div>
    );
};

export default HabitacionFiltros;
