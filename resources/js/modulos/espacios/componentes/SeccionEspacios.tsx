import { Building2, Flame } from 'lucide-react';
import { BuscadorFiltroCategorias } from '@/modulos/compartido/componentes/BuscadorFiltroCategorias';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import { Button } from '@/modulos/compartido/ui/boton';
import { useFiltroEspacios } from '../hooks/useFiltroEspacios';
import type { PropiedadesSeccionEspacios } from '../interfaces/espacioInterfaces';
import { ModalGaleriaEspacio } from './secciones/ModalGaleriaEspacio';
import { TarjetaEspacioItem } from './secciones/TarjetaEspacioItem';

export const SeccionEspacios = ({
    espacios = [],
    tipos = [],
    tipoSeleccionado = null,
}: PropiedadesSeccionEspacios) => {
    const {
        activeTipo,
        term,
        setTerm,
        modalGaleria,
        imgIndex,
        setImgIndex,
        espaciosFiltrados,
        handleSearchSubmit,
        handleFilterTipo,
        handleReset,
        abrirGaleria,
        cerrarGaleria,
    } = useFiltroEspacios(espacios, tipoSeleccionado);

    const listaTipos = tipos.map((t) => t.label || t.tipo);

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico Compacto Estilo Boutique */}
                <div className="mb-8">
                    <PortadaHeroGeneral
                        imagenFondo="/images/terrace.webp"
                        badgeLabel="Infraestructura Boutique"
                        badgeIcon={Building2}
                        badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300 dark:text-bugambilia-200"
                        titulo="Salones & Espacios para"
                        tituloEnfasis="Eventos"
                        descripcion="Reserve nuestros salones corporativos, terrazas tropicales y áreas gastronómicas con servicio 5 estrellas en Estelí, Nicaragua."
                        alturaClass="min-h-[220px] sm:min-h-[260px] md:min-h-[300px] rounded-3xl"
                    />
                </div>

                {/* Componente de Filtro Compartido por Categorías / Tipos de Espacio */}
                <div className="mb-8">
                    <BuscadorFiltroCategorias
                        busqueda={term}
                        onCambioBusqueda={setTerm}
                        onSubmitBusqueda={handleSearchSubmit}
                        placeholder="Buscar salón, terraza o área por nombre..."
                        categorias={listaTipos}
                        categoriaSeleccionada={activeTipo}
                        onSeleccionarCategoria={handleFilterTipo}
                        onLimpiar={handleReset}
                    />
                </div>

                {/* Carrusel Horizontal Móvil + Grilla Responsive Desktop */}
                {espaciosFiltrados.length > 0 ? (
                    <div>
                        {/* Indicador de Desplazamiento Móvil */}
                        <div className="mb-2 flex items-center justify-between text-xs font-bold text-muted-foreground sm:hidden">
                            <span>Deslice horizontalmente ↔</span>
                            <span>{espaciosFiltrados.length} espacios</span>
                        </div>

                        <div className="no-scrollbar flex w-full snap-x snap-mandatory gap-4 overflow-x-auto pb-4 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
                            {espaciosFiltrados.map((espacio) => (
                                <div
                                    key={espacio.id}
                                    className="w-[85vw] max-w-[320px] shrink-0 snap-center sm:w-auto sm:max-w-none sm:shrink"
                                >
                                    <TarjetaEspacioItem
                                        espacio={espacio}
                                        onVerGaleria={abrirGaleria}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
                        <Flame className="mb-3 size-12 text-muted-foreground/40" />
                        <h3 className="text-lg font-black text-foreground">
                            No se encontraron espacios
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Intente cambiar el término de búsqueda o seleccione
                            otra categoría.
                        </p>
                        <Button
                            onClick={handleReset}
                            variant="outline"
                            size="sm"
                            className="mt-4 rounded-full font-bold"
                        >
                            Ver Todos los Espacios
                        </Button>
                    </div>
                )}
            </div>

            {/* Modal de Galería de Fotografías */}
            <ModalGaleriaEspacio
                open={modalGaleria.open}
                espacio={modalGaleria.espacio}
                imgIndex={imgIndex}
                setImgIndex={setImgIndex}
                onClose={cerrarGaleria}
            />
        </section>
    );
};

export default SeccionEspacios;
