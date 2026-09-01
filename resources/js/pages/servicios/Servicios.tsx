import { Head } from '@inertiajs/react';
import { ConciergeBell, RotateCcw } from 'lucide-react';
import { ServicioCard } from '@/modules/servicios/components/ServicioCard';
import { ServicioFiltros } from '@/modules/servicios/components/ServicioFiltros';
import { ServicioHero } from '@/modules/servicios/components/ServicioHero';
import { useFiltrosServicios } from '@/modules/servicios/hooks/useFiltrosServicios';
import type { ServiciosPageProps } from '@/modules/servicios/types';
import { ServicioGridSkeleton } from '@/modules/shared/components/skeletons';
import { Button } from '@/modules/shared/components/ui/button';

export const Servicios = ({
    services = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
}: ServiciosPageProps) => {
    const {
        filtros,
        serviciosFiltrados,
        isPending,
        manejarCambioCategoria,
        manejarCambioBusqueda,
        manejarSubmitBusqueda,
        manejarReset,
    } = useFiltrosServicios({
        services,
        selectedCategory,
        searchQuery,
    });

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>
                    Servicios & Experiencias — Hotel Bugambilias Estelí
                </title>
                <meta
                    name="description"
                    content="Descubre todos los servicios de Hotel Bugambilias en Estelí: Restaurante Absoluto, piscina tropical, coctelería de autor y salones de eventos."
                />
            </Head>

            {/* Cabecera Hero */}
            <ServicioHero />

            {/* Filtros y Buscador */}
            <ServicioFiltros
                categorias={categorias}
                categoriaActiva={filtros.categoria}
                alSeleccionarCategoria={manejarCambioCategoria}
                busqueda={filtros.buscar}
                alCambiarBusqueda={manejarCambioBusqueda}
                alBuscar={manejarSubmitBusqueda}
                alLimpiar={manejarReset}
                totalResultados={serviciosFiltrados.length}
            />

            {/* Catálogo de Servicios */}
            <div className="container mx-auto px-4 py-12 sm:px-6">
                {isPending ? (
                    <ServicioGridSkeleton cantidad={3} />
                ) : serviciosFiltrados.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/40 py-16 text-center">
                        <ConciergeBell className="size-12 text-muted-foreground/40" />
                        <h3 className="mt-4 text-base font-black text-foreground">
                            No se encontraron servicios con estos filtros
                        </h3>
                        <p className="mt-1 max-w-sm text-xs text-muted-foreground">
                            Intenta con otra palabra clave o selecciona otra
                            categoría de servicios.
                        </p>
                        <Button
                            type="button"
                            onClick={manejarReset}
                            className="mt-5 cursor-pointer rounded-full bg-primary px-5 py-2 text-xs font-black text-primary-foreground shadow-sm hover:bg-primary/90"
                        >
                            <RotateCcw className="mr-1.5 size-3.5" />
                            <span>Limpiar filtros</span>
                        </Button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {serviciosFiltrados.map((servicio) => (
                            <ServicioCard
                                key={servicio.id}
                                servicio={servicio}
                            />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default Servicios;
