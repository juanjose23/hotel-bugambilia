import { ConciergeBell, Flame } from 'lucide-react';
import { BuscadorFiltroCategorias } from '@/modulos/compartido/componentes/BuscadorFiltroCategorias';
import { PaginadorPublico } from '@/modulos/compartido/componentes/PaginadorPublico';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import { Button } from '@/modulos/compartido/ui/boton';
import { useFiltroServicios } from '../hooks/useFiltroServicios';
import type { PropiedadesSeccionServicios } from '../interfaces/servicioInterfaces';
import { TarjetaServicioItem } from './secciones/TarjetaServicioItem';

export const SeccionServicios = ({
    services = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: PropiedadesSeccionServicios) => {
    const {
        term,
        setTerm,
        handleSearchSubmit,
        handleCategorySelect,
        handleReset,
    } = useFiltroServicios(searchQuery, selectedCategory);

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico Compacto Estilo Boutique */}
                <div className="mb-8">
                    <PortadaHeroGeneral
                        imagenFondo="/images/terrace.webp"
                        badgeLabel="Hospitalidad Premium"
                        badgeIcon={ConciergeBell}
                        badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300 dark:text-bugambilia-200"
                        titulo="Servicios Exclusivos &"
                        tituloEnfasis="Experiencias"
                        descripcion="Complemente su estancia en Estelí con comodidades boutique, atención de concierge 24/7 y servicios de transporte personalizado."
                        alturaClass="min-h-[220px] sm:min-h-[260px] md:min-h-[300px] rounded-3xl"
                    />
                </div>

                {/* Componente de Filtro Compartido Limpio por Categorías */}
                <div className="mb-8">
                    <BuscadorFiltroCategorias
                        busqueda={term}
                        onCambioBusqueda={setTerm}
                        onSubmitBusqueda={handleSearchSubmit}
                        placeholder="Buscar servicio por nombre o palabra clave..."
                        categorias={categorias}
                        categoriaSeleccionada={selectedCategory}
                        onSeleccionarCategoria={handleCategorySelect}
                        onLimpiar={handleReset}
                    />
                </div>

                {/* Carrusel Horizontal Móvil + Grilla Responsive Desktop */}
                {services.length > 0 ? (
                    <div>
                        {/* Indicador de Desplazamiento Móvil */}
                        <div className="mb-2 flex items-center justify-between text-xs font-bold text-muted-foreground sm:hidden">
                            <span>Deslice horizontalmente ↔</span>
                            <span>{services.length} servicios</span>
                        </div>

                        <div className="no-scrollbar flex w-full snap-x snap-mandatory gap-4 overflow-x-auto pb-4 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
                            {services.map((servicio) => (
                                <div
                                    key={servicio.id}
                                    className="w-[85vw] max-w-[320px] shrink-0 snap-center sm:w-auto sm:max-w-none sm:shrink"
                                >
                                    <TarjetaServicioItem servicio={servicio} />
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
                        <Flame className="mb-3 size-12 text-muted-foreground/40" />
                        <h3 className="text-lg font-black text-foreground">
                            No se encontraron servicios
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Pruebe a cambiar los términos de búsqueda o
                            seleccionar otra categoría.
                        </p>
                        <Button
                            onClick={handleReset}
                            variant="outline"
                            size="sm"
                            className="mt-4 rounded-full font-bold"
                        >
                            Ver Todos los Servicios
                        </Button>
                    </div>
                )}

                {/* Paginación Reutilizable */}
                {pagination && (
                    <div className="mt-12">
                        <PaginadorPublico paginacion={pagination} />
                    </div>
                )}
            </div>
        </section>
    );
};

export default SeccionServicios;
