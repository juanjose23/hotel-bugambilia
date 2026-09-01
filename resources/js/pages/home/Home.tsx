import { Head, Link } from '@inertiajs/react';
import { Hotel, RotateCcw, LayoutGrid, SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import { BookingCtaBanner } from '@/modules/home/components/BookingCtaBanner';
import { CategoryBar } from '@/modules/home/components/CategoryBar';
import { FilterSheet } from '@/modules/home/components/FilterSheet';
import { Hero } from '@/modules/home/components/Hero';
import { ServicesSection } from '@/modules/home/components/ServicesSection';
import type { PropiedadesHomePage } from '@/modules/home/types';
import { RoomCard } from '@/modules/shared/components/RoomCard';
import { Button } from '@/modules/shared/components/ui/button';
import { useFiltrosHabitaciones } from '@/modules/shared/hooks/useFiltrosHabitaciones';

export const Home = ({
    habitaciones = [],
    servicios = [],
    espacios = [],
    filtrosDisponibles = {
        categorias: [],
        servicios: [],
        capacidades: [1, 2, 3, 4],
        precioMin: 35,
        precioMax: 150,
    },
}: PropiedadesHomePage) => {
    const {
        filtros,
        setFiltros,
        sheetFiltrosAbierto,
        setSheetFiltrosAbierto,
        totalFiltrosActivos,
        habitacionesFiltradas,
        manejarReset,
    } = useFiltrosHabitaciones({ habitaciones, filtrosDisponibles });

    // Toggle de visualización en móvil (Carrusel horizontal vs Cuadrícula vertical)
    const [vistaMobileGrid, setVistaMobileGrid] = useState(false);

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>
                    Hotel Bugambilias — Estancia & Restaurante en Estelí,
                    Nicaragua
                </title>
                <meta
                    name="description"
                    content="Reserva en Hotel Bugambilias Estelí. Habitaciones de lujo, piscina tropical, restaurante Absoluto y servicio de primera categoría."
                />
            </Head>

            {/* Hero Principal con Cápsula de Búsqueda RHF */}
            <Hero categorias={filtrosDisponibles.categorias} />

            {/* Barra de Categorías Dinámicas con Botón de Filtros */}
            <CategoryBar
                categoriaActiva={filtros.categoria}
                alSeleccionarCategoria={(cat) =>
                    setFiltros((prev) => ({ ...prev, categoria: cat }))
                }
                categorias={filtrosDisponibles.categorias}
                alAbrirFiltros={() => setSheetFiltrosAbierto(true)}
                totalFiltrosActivos={totalFiltrosActivos}
            />

            {/* Sheet Lateral con Filtros Dinámicos */}
            <FilterSheet
                abierto={sheetFiltrosAbierto}
                alCerrar={() => setSheetFiltrosAbierto(false)}
                filtros={filtros}
                alCambiarFiltros={setFiltros}
                alResetearFiltros={manejarReset}
                filtrosDisponibles={filtrosDisponibles}
                totalResultados={habitacionesFiltradas.length}
            />

            {/* Listado de Habitaciones con Soporte Anti-Scroll Móvil */}
            <section
                id="seccion-habitaciones"
                aria-label="Catálogo de Habitaciones y Suites"
                className="py-6 md:py-10"
            >
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="mb-5 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-black tracking-tight text-foreground sm:text-2xl">
                                Habitaciones & Suites
                            </h2>
                            <p className="text-xs text-muted-foreground sm:text-sm">
                                {habitacionesFiltradas.length}{' '}
                                {habitacionesFiltradas.length === 1
                                    ? 'opción disponible en Estelí'
                                    : 'opciones disponibles en Estelí'}
                            </p>
                        </div>

                        {/* Botón de alternancia de vista móvil */}
                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() =>
                                    setVistaMobileGrid(!vistaMobileGrid)
                                }
                                aria-label={
                                    vistaMobileGrid
                                        ? 'Cambiar a carrusel'
                                        : 'Cambiar a cuadrícula'
                                }
                                className="flex size-9 rounded-full sm:hidden"
                            >
                                {vistaMobileGrid ? (
                                    <SlidersHorizontal className="size-4" />
                                ) : (
                                    <LayoutGrid className="size-4" />
                                )}
                            </Button>
                        </div>
                    </div>

                    {/* Estados de Resultados */}
                    {habitacionesFiltradas.length === 0 ? (
                        <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/50 py-12 text-center">
                            <Hotel className="size-10 text-muted-foreground/50" />
                            <h3 className="mt-3 text-sm font-bold text-foreground">
                                No se encontraron habitaciones
                            </h3>
                            <p className="mt-1 max-w-xs text-xs text-muted-foreground">
                                Prueba ajustando los filtros de precio,
                                capacidad o amenidades.
                            </p>
                            <Button
                                type="button"
                                onClick={manejarReset}
                                className="mt-4 inline-flex cursor-pointer items-center gap-1.5 rounded-full px-4 py-2 text-xs font-black shadow-xs active:scale-95"
                            >
                                <RotateCcw className="size-3" />
                                <span>Restablecer filtros</span>
                            </Button>
                        </div>
                    ) : vistaMobileGrid ? (
                        /* Vista Cuadrícula en Móvil */
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {habitacionesFiltradas.map((room) => (
                                <RoomCard key={room.id} room={room} />
                            ))}
                        </div>
                    ) : (
                        /* Vista Carrusel Swipe Horizontal en Móvil / Grid en Desktop */
                        <div className="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
                            {habitacionesFiltradas.map((room) => (
                                <div
                                    key={room.id}
                                    className="w-[280px] shrink-0 snap-center sm:w-auto"
                                >
                                    <RoomCard room={room} />
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Botón Ver Todas */}
                    {habitacionesFiltradas.length > 0 && (
                        <div className="mt-6 flex justify-center">
                            <Link
                                href="/habitaciones"
                                className="inline-flex items-center gap-2 rounded-full border border-border bg-card px-6 py-2 text-xs font-black tracking-wider text-foreground uppercase shadow-xs transition-all hover:bg-muted active:scale-95"
                            >
                                <Hotel className="size-3.5 text-primary dark:text-rose-400" />
                                <span>Ver todas las habitaciones</span>
                            </Link>
                        </div>
                    )}
                </div>
            </section>

            {/* Espacios Bugambilias & Amenidades desde BD */}
            <ServicesSection espacios={espacios} servicios={servicios} />

            {/* Banner de Reserva Directa & Preguntas Frecuentes */}
            <BookingCtaBanner />
        </div>
    );
};

export default Home;
