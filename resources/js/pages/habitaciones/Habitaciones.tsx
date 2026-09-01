import { Head } from '@inertiajs/react';
import {
    BedDouble,
    RotateCcw,
    LayoutGrid,
    SlidersHorizontal,
} from 'lucide-react';
import { useState } from 'react';
import { HabitacionFiltros } from '@/modules/habitaciones/components/HabitacionFiltros';
import { HabitacionHero } from '@/modules/habitaciones/components/HabitacionHero';
import { useFiltrosHabitacionesPage } from '@/modules/habitaciones/hooks/useFiltrosHabitacionesPage';
import type { HabitacionesPageProps } from '@/modules/habitaciones/types';
import { RoomCard } from '@/modules/shared/components/RoomCard';
import { RoomGridSkeleton } from '@/modules/shared/components/skeletons';
import { Button } from '@/modules/shared/components/ui/button';

export const Habitaciones = ({
    rooms = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
}: HabitacionesPageProps) => {
    const [vistaMobileGrid, setVistaMobileGrid] = useState(false);
    const {
        categoria,
        buscar,
        huespedes,
        orden,
        isPending,
        habitacionesFiltradas,
        setBuscar,
        setHuespedes,
        setOrden,
        manejarCambioCategoria,
        manejarSubmitBusqueda,
        manejarReset,
    } = useFiltrosHabitacionesPage({
        rooms,
        selectedCategory,
        searchQuery,
    });

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>Habitaciones & Suites — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Explora nuestras habitaciones y suites de lujo en Hotel Bugambilias Estelí. Aire acondicionado, WiFi, piscina y confort superior."
                />
            </Head>

            {/* Cabecera Hero */}
            <HabitacionHero totalHabitaciones={rooms.length} />

            {/* Barra de Filtros Interactiva */}
            <HabitacionFiltros
                categorias={categorias}
                categoriaActiva={categoria}
                alSeleccionarCategoria={manejarCambioCategoria}
                busqueda={buscar}
                alCambiarBusqueda={setBuscar}
                alBuscar={manejarSubmitBusqueda}
                huespedes={huespedes}
                alCambiarHuespedes={setHuespedes}
                orden={orden}
                alCambiarOrden={setOrden}
                alLimpiar={manejarReset}
                totalResultados={habitacionesFiltradas.length}
            />

            {/* Rejilla / Carrusel Horizontal de Habitaciones */}
            <div className="container mx-auto px-4 py-8 sm:px-6 sm:py-10">
                {isPending ? (
                    <RoomGridSkeleton cantidad={4} />
                ) : habitacionesFiltradas.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/40 py-16 text-center">
                        <BedDouble className="size-12 text-muted-foreground/40" />
                        <h3 className="mt-4 text-base font-black text-foreground">
                            No encontramos habitaciones disponibles
                        </h3>
                        <p className="mt-1 max-w-sm text-xs text-muted-foreground">
                            Prueba ajustando los filtros de categoría, cantidad
                            de huéspedes o término de búsqueda.
                        </p>
                        <Button
                            type="button"
                            onClick={manejarReset}
                            className="mt-5 cursor-pointer rounded-full bg-primary px-5 py-2 text-xs font-black text-primary-foreground shadow-sm hover:bg-primary/90"
                        >
                            <RotateCcw className="mr-1.5 size-3.5" />
                            <span>Restablecer todos los filtros</span>
                        </Button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {/* Cabecera móvil con indicador y alternador de vista */}
                        <div className="flex items-center justify-between text-xs font-bold text-muted-foreground sm:hidden">
                            <span>
                                {vistaMobileGrid
                                    ? 'Vista en cuadrícula'
                                    : 'Desliza para ver suites →'}
                            </span>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    setVistaMobileGrid(!vistaMobileGrid)
                                }
                                className="h-7 rounded-full px-2.5 text-[11px] font-bold"
                            >
                                {vistaMobileGrid ? (
                                    <>
                                        <SlidersHorizontal className="mr-1 size-3" />
                                        <span>Carrusel</span>
                                    </>
                                ) : (
                                    <>
                                        <LayoutGrid className="mr-1 size-3" />
                                        <span>Cuadrícula</span>
                                    </>
                                )}
                            </Button>
                        </div>

                        {vistaMobileGrid ? (
                            /* Vista Cuadrícula Vertical en Móvil */
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {habitacionesFiltradas.map((room) => (
                                    <RoomCard key={room.id} room={room} />
                                ))}
                            </div>
                        ) : (
                            /* Vista Carrusel Horizontal Snap en Móvil / Cuadrícula en Desktop */
                            <div className="-mx-4 flex snap-x snap-mandatory scrollbar-none gap-4 overflow-x-auto px-4 pb-6 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
                                {habitacionesFiltradas.map((room) => (
                                    <div
                                        key={room.id}
                                        className="w-[84vw] max-w-[340px] shrink-0 snap-center sm:w-auto sm:max-w-none sm:shrink"
                                    >
                                        <RoomCard room={room} />
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
};

export default Habitaciones;
