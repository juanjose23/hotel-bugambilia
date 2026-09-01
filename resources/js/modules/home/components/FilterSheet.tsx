import { SlidersHorizontal, RotateCcw } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/modules/shared/components/ui/sheet';
import type {
    FiltrosActivos,
    FiltrosDisponiblesDomain,
} from '@/modules/shared/types';

export type {
    CategoriaFiltro,
    ServicioFiltro,
    FiltrosDisponiblesDomain,
    FiltrosActivos,
} from '@/modules/shared/types';

interface PropsFilterSheet {
    abierto: boolean;
    alCerrar: () => void;
    filtros: FiltrosActivos;
    alCambiarFiltros: (nuevosFiltros: FiltrosActivos) => void;
    alResetearFiltros: () => void;
    filtrosDisponibles: FiltrosDisponiblesDomain;
    totalResultados: number;
}

export const FilterSheet = ({
    abierto,
    alCerrar,
    filtros,
    alCambiarFiltros,
    alResetearFiltros,
    filtrosDisponibles,
    totalResultados,
}: PropsFilterSheet) => {
    const toggleServicio = (id: string | number) => {
        const nuevos = filtros.serviciosIds.includes(id)
            ? filtros.serviciosIds.filter((sId) => sId !== id)
            : [...filtros.serviciosIds, id];
        alCambiarFiltros({ ...filtros, serviciosIds: nuevos });
    };

    return (
        <Sheet open={abierto} onOpenChange={(val) => !val && alCerrar()}>
            <SheetContent
                side="right"
                className="flex flex-col p-0 sm:max-w-md"
            >
                {/* Cabecera */}
                <SheetHeader className="border-b border-border p-5">
                    <div className="flex items-center gap-2">
                        <SlidersHorizontal className="size-4 text-bugambilia-500" />
                        <SheetTitle>Filtros de Búsqueda</SheetTitle>
                    </div>
                    <SheetDescription>
                        Ajusta tus preferencias en tiempo real con datos del
                        hotel.
                    </SheetDescription>
                </SheetHeader>

                {/* Contenido */}
                <div className="flex-1 scrollbar-none space-y-5 overflow-y-auto p-5">
                    {/* Rango de Precios con shadcn Input */}
                    <div>
                        <span className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                            Precio por noche ($)
                        </span>
                        <div className="mt-2 flex items-center gap-3">
                            <Input
                                type="number"
                                min={filtrosDisponibles.precioMin}
                                max={filtros.precioMax}
                                value={filtros.precioMin}
                                onChange={(e) =>
                                    alCambiarFiltros({
                                        ...filtros,
                                        precioMin: Number(e.target.value),
                                    })
                                }
                                className="h-9 font-bold"
                                placeholder="Mínimo"
                            />
                            <span className="text-xs text-muted-foreground">
                                —
                            </span>
                            <Input
                                type="number"
                                min={filtros.precioMin}
                                max={filtrosDisponibles.precioMax}
                                value={filtros.precioMax}
                                onChange={(e) =>
                                    alCambiarFiltros({
                                        ...filtros,
                                        precioMax: Number(e.target.value),
                                    })
                                }
                                className="h-9 font-bold"
                                placeholder="Máximo"
                            />
                        </div>
                    </div>

                    {/* Categorías */}
                    {filtrosDisponibles.categorias.length > 0 && (
                        <div>
                            <span className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                Tipo de Habitación
                            </span>
                            <div className="mt-2 flex flex-wrap gap-1.5">
                                {[
                                    { id: 'todas', nombre: 'Todas' },
                                    ...filtrosDisponibles.categorias,
                                ].map((c) => {
                                    const activo =
                                        filtros.categoria.toLowerCase() ===
                                        c.nombre.toLowerCase();

                                    return (
                                        <Button
                                            key={c.id}
                                            type="button"
                                            variant={
                                                activo ? 'default' : 'outline'
                                            }
                                            size="sm"
                                            onClick={() =>
                                                alCambiarFiltros({
                                                    ...filtros,
                                                    categoria: c.nombre,
                                                })
                                            }
                                            className={`rounded-full text-xs font-bold ${
                                                activo
                                                    ? 'bg-foreground text-background shadow-xs'
                                                    : ''
                                            }`}
                                        >
                                            {c.nombre}
                                        </Button>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Capacidad */}
                    <div>
                        <span className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                            Huéspedes
                        </span>
                        <div className="mt-2 flex gap-1.5">
                            {[0, 1, 2, 3, 4].map((num) => (
                                <Button
                                    key={num}
                                    type="button"
                                    variant={
                                        filtros.capacidad === num
                                            ? 'default'
                                            : 'outline'
                                    }
                                    size="sm"
                                    onClick={() =>
                                        alCambiarFiltros({
                                            ...filtros,
                                            capacidad: num,
                                        })
                                    }
                                    className="flex-1 rounded-xl text-xs font-bold"
                                >
                                    {num === 0 ? 'Todos' : `${num}+`}
                                </Button>
                            ))}
                        </div>
                    </div>

                    {/* Servicios y Amenidades */}
                    {filtrosDisponibles.servicios.length > 0 && (
                        <div>
                            <span className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                Servicios Incluidos
                            </span>
                            <div className="mt-2 flex flex-wrap gap-1.5">
                                {filtrosDisponibles.servicios.map((s) => {
                                    const activo =
                                        filtros.serviciosIds.includes(s.id);

                                    return (
                                        <Button
                                            key={s.id}
                                            type="button"
                                            variant={
                                                activo ? 'default' : 'outline'
                                            }
                                            size="sm"
                                            onClick={() => toggleServicio(s.id)}
                                            className={`rounded-full text-xs font-bold ${
                                                activo
                                                    ? 'bg-bugambilia-600 font-black text-white shadow-xs hover:bg-bugambilia-700'
                                                    : ''
                                            }`}
                                        >
                                            {s.nombre}
                                        </Button>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>

                {/* Pie */}
                <div className="flex items-center justify-between border-t border-border bg-muted/20 p-5">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={alResetearFiltros}
                        className="inline-flex cursor-pointer items-center gap-1.5 text-xs font-bold text-muted-foreground hover:text-foreground"
                    >
                        <RotateCcw className="size-3.5" />
                        <span>Limpiar</span>
                    </Button>

                    <Button
                        type="button"
                        onClick={alCerrar}
                        className="cursor-pointer rounded-full bg-bugambilia-600 px-5 py-2 text-xs font-black text-white shadow-md hover:bg-bugambilia-700 active:scale-95"
                    >
                        <span>Ver {totalResultados} habitaciones</span>
                    </Button>
                </div>
            </SheetContent>
        </Sheet>
    );
};

export default FilterSheet;
