import { Search, FilterX, ChevronLeft, ChevronRight } from 'lucide-react';
import { useRef, useCallback, useState, useEffect } from 'react';
import type { SyntheticEvent, ReactNode } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesBuscadorFiltroCategorias {
    busqueda: string;
    onCambioBusqueda: (val: string) => void;
    onSubmitBusqueda?: (e: SyntheticEvent) => void;
    placeholder?: string;
    categorias: string[];
    categoriaSeleccionada: string | null;
    onSeleccionarCategoria: (cat: string | null) => void;
    onLimpiar?: () => void;
    claseContenedor?: string;
    filtrosAdicionales?: ReactNode;
}

export const BuscadorFiltroCategorias = ({
    busqueda,
    onCambioBusqueda,
    onSubmitBusqueda,
    placeholder = 'Buscar por nombre o palabra clave...',
    categorias = [],
    categoriaSeleccionada = null,
    onSeleccionarCategoria,
    onLimpiar,
    claseContenedor = '',
    filtrosAdicionales,
}: PropiedadesBuscadorFiltroCategorias) => {
    const hayFiltrosActivos = Boolean(busqueda || categoriaSeleccionada);
    const contenedorScrollRef = useRef<HTMLDivElement>(null);

    const [puedeHacerScrollIzquierda, setPuedeHacerScrollIzquierda] =
        useState(false);
    const [puedeHacerScrollDerecha, setPuedeHacerScrollDerecha] =
        useState(false);

    const verificarScroll = useCallback(() => {
        const el = contenedorScrollRef.current;

        if (!el) {
            return;
        }

        setPuedeHacerScrollIzquierda(el.scrollLeft > 5);
        setPuedeHacerScrollDerecha(
            el.scrollLeft < el.scrollWidth - el.clientWidth - 5,
        );
    }, []);

    useEffect(() => {
        const el = contenedorScrollRef.current;

        if (!el) {
            return;
        }

        verificarScroll();
        el.addEventListener('scroll', verificarScroll);
        window.addEventListener('resize', verificarScroll);

        return () => {
            el.removeEventListener('scroll', verificarScroll);
            window.removeEventListener('resize', verificarScroll);
        };
    }, [verificarScroll, categorias]);

    const desplazarHorizontal = (direccion: 'izquierda' | 'derecha') => {
        if (!contenedorScrollRef.current) {
            return;
        }

        const delta = direccion === 'izquierda' ? -260 : 260;
        contenedorScrollRef.current.scrollBy({
            left: delta,
            behavior: 'smooth',
        });
    };

    const autoScrollSeleccionado = useCallback(
        (node: HTMLButtonElement | null) => {
            if (node && node.dataset.selected === 'true') {
                node.scrollIntoView({
                    behavior: 'smooth',
                    inline: 'center',
                    block: 'nearest',
                });
            }
        },
        [],
    );

    return (
        <div
            className={`flex flex-col gap-4 rounded-3xl border border-border/80 bg-card p-4 font-sans shadow-sm ${claseContenedor}`}
        >
            {/* Fila 1: Barra de Búsqueda Principal + Filtros Adicionales & Botón Limpiar */}
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();

                        if (onSubmitBusqueda) {
                            onSubmitBusqueda(e);
                        }
                    }}
                    className="relative flex max-w-xl flex-1 items-center"
                >
                    <Search className="absolute left-3.5 size-4 text-muted-foreground" />
                    <Input
                        type="text"
                        value={busqueda}
                        onChange={(e) => onCambioBusqueda(e.target.value)}
                        placeholder={placeholder}
                        className="rounded-2xl border-border/80 pr-24 pl-10 text-xs font-medium focus-visible:ring-bugambilia-500"
                    />
                    {onSubmitBusqueda && (
                        <Button
                            type="submit"
                            size="xs"
                            className="absolute right-1.5 rounded-xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:hover:bg-bugambilia-600"
                        >
                            Buscar
                        </Button>
                    )}
                </form>

                <div className="flex items-center gap-2">
                    {filtrosAdicionales}

                    {hayFiltrosActivos && onLimpiar && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={onLimpiar}
                            className="shrink-0 rounded-full text-xs font-bold text-rose-500 hover:bg-rose-500/10"
                        >
                            <FilterX className="mr-1.5 size-3.5" /> Limpiar
                            Filtros
                        </Button>
                    )}
                </div>
            </div>

            {/* Fila 2: Carousel de Categorías de Ancho Completo con Gradientes y Botones Flotantes */}
            {categorias.length > 0 && (
                <div className="relative flex items-center border-t border-border/40 pt-3">
                    {/* Botón Flotante Izquierdo */}
                    {puedeHacerScrollIzquierda && (
                        <div className="absolute left-0 z-20 flex items-center bg-gradient-to-r from-card via-card/90 to-transparent py-1 pr-4">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() => desplazarHorizontal('izquierda')}
                                className="size-8 rounded-full border-border/80 bg-card text-foreground shadow-md transition-transform hover:scale-105 hover:bg-muted"
                                title="Anterior"
                            >
                                <ChevronLeft className="size-4" />
                            </Button>
                        </div>
                    )}

                    {/* Contenedor de Scroll de Categorías */}
                    <div
                        ref={contenedorScrollRef}
                        onWheel={(e) => {
                            if (e.deltaY !== 0 && contenedorScrollRef.current) {
                                contenedorScrollRef.current.scrollLeft +=
                                    e.deltaY;
                            }
                        }}
                        className="no-scrollbar flex w-full items-center gap-2 overflow-x-auto scroll-smooth px-1 py-1"
                    >
                        {(() => {
                            const esTodas =
                                !categoriaSeleccionada ||
                                categoriaSeleccionada.trim() === '' ||
                                categoriaSeleccionada.toLowerCase() === 'todas';

                            return (
                                <Button
                                    type="button"
                                    ref={
                                        esTodas
                                            ? autoScrollSeleccionado
                                            : undefined
                                    }
                                    data-selected={esTodas ? 'true' : 'false'}
                                    variant={esTodas ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => onSeleccionarCategoria(null)}
                                    className={`shrink-0 rounded-full text-xs font-extrabold transition-all ${
                                        esTodas
                                            ? 'bg-bugambilia-600 text-white shadow-xs hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:text-white'
                                            : 'border-border/80 text-muted-foreground hover:border-bugambilia-500/40 hover:text-foreground'
                                    }`}
                                >
                                    Todas las categorías
                                </Button>
                            );
                        })()}

                        {categorias.map((cat) => {
                            const isSelected =
                                categoriaSeleccionada?.toLowerCase() ===
                                cat.toLowerCase();

                            return (
                                <Button
                                    key={cat}
                                    type="button"
                                    ref={
                                        isSelected
                                            ? autoScrollSeleccionado
                                            : undefined
                                    }
                                    data-selected={
                                        isSelected ? 'true' : 'false'
                                    }
                                    variant={isSelected ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() =>
                                        onSeleccionarCategoria(
                                            isSelected ? null : cat,
                                        )
                                    }
                                    className={`shrink-0 rounded-full text-xs font-extrabold transition-all ${
                                        isSelected
                                            ? 'bg-bugambilia-600 text-white shadow-xs hover:bg-bugambilia-700 dark:bg-bugambilia-500 dark:text-white'
                                            : 'border-border/80 text-muted-foreground hover:border-bugambilia-500/40 hover:text-foreground'
                                    }`}
                                >
                                    {cat}
                                </Button>
                            );
                        })}
                    </div>

                    {/* Botón Flotante Derecho */}
                    {puedeHacerScrollDerecha && (
                        <div className="absolute right-0 z-20 flex items-center bg-gradient-to-l from-card via-card/90 to-transparent py-1 pl-4">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() => desplazarHorizontal('derecha')}
                                className="size-8 rounded-full border-border/80 bg-card text-foreground shadow-md transition-transform hover:scale-105 hover:bg-muted"
                                title="Siguiente"
                            >
                                <ChevronRight className="size-4" />
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};
