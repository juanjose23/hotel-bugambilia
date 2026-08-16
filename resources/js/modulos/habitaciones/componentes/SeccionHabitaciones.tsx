import { BadgeCheck, SlidersHorizontal, RotateCcw, Users } from 'lucide-react';
import { useState } from 'react';
import { BuscadorFiltroCategorias } from '@/modulos/compartido/componentes/BuscadorFiltroCategorias';
import { PaginadorPublico } from '@/modulos/compartido/componentes/PaginadorPublico';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import type { DatosPaginacion } from '@/modulos/compartido/types';
import { Button } from '@/modulos/compartido/ui/boton';
import { useFiltroHabitaciones } from '../hooks/useFiltroHabitaciones';
import type { HabitacionGrupo } from '../interfaces/habitacionInterfaces';
import { TarjetaHabitacion } from './TarjetaHabitacion';

interface PropiedadesSeccionHabitaciones {
    rooms?: HabitacionGrupo[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}

export const SeccionHabitaciones = ({
    rooms = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: PropiedadesSeccionHabitaciones) => {
    const [mostrarFiltrosAvanzados, setMostrarFiltrosAvanzados] =
        useState(false);

    const {
        categoriaSel,
        busqueda,
        setBusqueda,
        filtroCapacidad,
        setFiltroCapacidad,
        precioMax,
        setPrecioMax,
        habitacionesFiltradas,
        handleSearchSubmit,
        handleSeleccionarCategoria,
        limpiarFiltros,
    } = useFiltroHabitaciones(rooms, selectedCategory, searchQuery);

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico Compacto Estilo Boutique */}
                <div className="mb-6">
                    <PortadaHeroGeneral
                        imagenFondo="/images/hero-main.webp"
                        badgeLabel="Catálogo Boutique en Estelí"
                        badgeIcon={BadgeCheck}
                        badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300 dark:text-bugambilia-200"
                        titulo="Habitaciones &"
                        tituloEnfasis="Suites Exclusivas"
                        descripcion="Descubra la opción ideal para su estancia en Estelí. Confort de primera clase, climatización y tranquilidad garantizada."
                        alturaClass="min-h-[220px] sm:min-h-[260px] md:min-h-[300px] rounded-3xl"
                    />
                </div>

                {/* Componente de Filtro Compartido del Sistema */}
                <div className="mb-6">
                    <BuscadorFiltroCategorias
                        busqueda={busqueda}
                        onCambioBusqueda={setBusqueda}
                        onSubmitBusqueda={handleSearchSubmit}
                        placeholder="Buscar por nombre o característica..."
                        categorias={categorias}
                        categoriaSeleccionada={categoriaSel}
                        onSeleccionarCategoria={handleSeleccionarCategoria}
                        onLimpiar={limpiarFiltros}
                    />
                </div>

                {/* Controles de Filtro Avanzado (Capacidad / Presupuesto) */}
                <div className="mb-8 flex items-center justify-between">
                    <Button
                        type="button"
                        variant="ghost"
                        size="xs"
                        onClick={() =>
                            setMostrarFiltrosAvanzados(!mostrarFiltrosAvanzados)
                        }
                        className="cursor-pointer rounded-full text-xs font-extrabold text-bugambilia-600 hover:bg-bugambilia-500/10 dark:text-bugambilia-400"
                    >
                        <SlidersHorizontal className="mr-1.5 size-3.5" />
                        {mostrarFiltrosAvanzados
                            ? 'Ocultar Filtros Avanzados'
                            : 'Filtros Avanzados (Capacidad / Precio)'}
                    </Button>
                </div>

                {/* Drawer / Panel de Filtros Avanzados */}
                {mostrarFiltrosAvanzados && (
                    <div className="mb-8 grid grid-cols-1 gap-4 rounded-3xl border border-border/80 bg-card p-5 shadow-xs sm:grid-cols-2 md:grid-cols-3">
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[11px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Mínimo Huéspedes
                            </label>
                            <div className="flex flex-wrap gap-1.5">
                                {[null, 1, 2, 4, 6].map((cap) => (
                                    <Button
                                        key={cap ?? 0}
                                        type="button"
                                        variant={
                                            filtroCapacidad === cap
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="xs"
                                        onClick={() => setFiltroCapacidad(cap)}
                                        className={`rounded-full text-xs font-bold ${
                                            filtroCapacidad === cap
                                                ? 'bg-bugambilia-600 text-white'
                                                : ''
                                        }`}
                                    >
                                        {cap === null
                                            ? 'Todos'
                                            : `${cap}+ pers.`}
                                    </Button>
                                ))}
                            </div>
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <label className="text-[11px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Precio Máximo (
                                {precioMax ? `$${precioMax}` : 'Sin límite'})
                            </label>
                            <input
                                type="range"
                                min="20"
                                max="300"
                                step="10"
                                value={precioMax || 300}
                                onChange={(e) =>
                                    setPrecioMax(Number(e.target.value))
                                }
                                className="cursor-pointer accent-bugambilia-600"
                            />
                        </div>

                        <div className="flex items-end sm:col-span-2 md:col-span-1">
                            <Button
                                type="button"
                                variant="outline"
                                size="xs"
                                onClick={limpiarFiltros}
                                className="w-full rounded-2xl border-border/80 font-bold"
                            >
                                <RotateCcw className="mr-1.5 size-3.5" />{' '}
                                Restablecer Filtros
                            </Button>
                        </div>
                    </div>
                )}

                {/* Carrusel Horizontal Móvil + Grilla Responsive Desktop */}
                {habitacionesFiltradas.length > 0 ? (
                    <div>
                        {/* Indicador de Desplazamiento Móvil */}
                        <div className="mb-2 flex items-center justify-between text-xs font-bold text-muted-foreground sm:hidden">
                            <span>Deslice horizontalmente ↔</span>
                            <span>
                                {habitacionesFiltradas.length} habitaciones
                            </span>
                        </div>

                        <div className="no-scrollbar flex w-full snap-x snap-mandatory gap-4 overflow-x-auto pb-4 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
                            {habitacionesFiltradas.map((habitacion) => (
                                <div
                                    key={habitacion.id}
                                    className="w-[85vw] max-w-[320px] shrink-0 snap-center sm:w-auto sm:max-w-none sm:shrink"
                                >
                                    <TarjetaHabitacion
                                        habitacion={habitacion}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
                        <Users className="mb-3 size-12 text-muted-foreground/40" />
                        <h3 className="text-lg font-black text-foreground">
                            No se encontraron habitaciones
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Intente ajustar los criterios de búsqueda o
                            capacidad.
                        </p>
                        <Button
                            onClick={limpiarFiltros}
                            variant="outline"
                            size="sm"
                            className="mt-4 rounded-full font-bold"
                        >
                            Limpiar Filtros
                        </Button>
                    </div>
                )}

                {/* Paginador Publico */}
                {pagination && (
                    <div className="mt-12">
                        <PaginadorPublico paginacion={pagination} />
                    </div>
                )}
            </div>
        </section>
    );
};

export default SeccionHabitaciones;
