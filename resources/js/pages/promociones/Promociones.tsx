import { Head } from '@inertiajs/react';
import { Gift, RotateCcw } from 'lucide-react';
import { useState } from 'react';
import { PromocionCard } from '@/modules/promociones/components/PromocionCard';
import { PromocionConsultaSheet } from '@/modules/promociones/components/PromocionConsultaSheet';
import { PromocionFiltros } from '@/modules/promociones/components/PromocionFiltros';
import { PromocionHero } from '@/modules/promociones/components/PromocionHero';
import { useFiltrosPromociones } from '@/modules/promociones/hooks/useFiltrosPromociones';
import type {
    PromocionesPageProps,
    PromocionItem,
} from '@/modules/promociones/types';
import { PromocionGridSkeleton } from '@/modules/shared/components/skeletons';
import { Button } from '@/modules/shared/components/ui/button';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const Promociones = ({
    promociones = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
}: PromocionesPageProps) => {
    const { hotel } = usePropiedadesPagina();
    const telefonoWhatsApp = (hotel?.whatsapp || '+50587136805').replace(
        /\D/g,
        '',
    );

    const [promocionSeleccionada, setPromocionSeleccionada] =
        useState<PromocionItem | null>(null);

    const {
        categoria,
        buscar,
        isPending,
        setBuscar,
        promocionesFiltradas,
        manejarCambioCategoria,
        manejarSubmitBusqueda,
        manejarReset,
    } = useFiltrosPromociones({
        promociones,
        selectedCategory,
        searchQuery,
    });

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>
                    Promociones & Paquetes Especiales — Hotel Bugambilias Estelí
                </title>
                <meta
                    name="description"
                    content="Descubre nuestras promociones de temporada, ofertas de fin de semana y paquetes corporativos con descuentos de hasta 30% en Hotel Bugambilias."
                />
            </Head>

            {/* Portada Hero */}
            <PromocionHero totalPromociones={promociones.length} />

            {/* Barra de Filtros */}
            <PromocionFiltros
                categorias={categorias}
                categoriaActiva={categoria}
                alSeleccionarCategoria={manejarCambioCategoria}
                busqueda={buscar}
                alCambiarBusqueda={setBuscar}
                alBuscar={(e) => {
                    e.preventDefault();
                    manejarSubmitBusqueda();
                }}
                alLimpiar={manejarReset}
                totalResultados={promocionesFiltradas.length}
            />

            {/* Listado de Promociones */}
            <div className="container mx-auto px-4 py-12 sm:px-6">
                {isPending ? (
                    <PromocionGridSkeleton cantidad={3} />
                ) : promocionesFiltradas.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/40 py-16 text-center">
                        <Gift className="size-12 text-muted-foreground/40" />
                        <h3 className="mt-4 text-base font-black text-foreground">
                            No se encontraron promociones con estos criterios
                        </h3>
                        <p className="mt-1 max-w-sm text-xs text-muted-foreground">
                            Prueba seleccionando otra categoría o limpiando la
                            búsqueda.
                        </p>
                        <Button
                            type="button"
                            onClick={manejarReset}
                            className="mt-5 cursor-pointer rounded-full bg-primary px-5 py-2 text-xs font-black text-primary-foreground shadow-sm hover:bg-primary/90"
                        >
                            <RotateCcw className="mr-1.5 size-3.5" />
                            <span>Ver todas las ofertas</span>
                        </Button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {promocionesFiltradas.map((promo) => (
                            <PromocionCard
                                key={promo.id}
                                promocion={promo}
                                alSeleccionar={setPromocionSeleccionada}
                                telefonoWhatsApp={telefonoWhatsApp}
                            />
                        ))}
                    </div>
                )}
            </div>

            {/* Sheet Lateral de Consulta */}
            <PromocionConsultaSheet
                abierto={promocionSeleccionada !== null}
                alCerrar={() => setPromocionSeleccionada(null)}
                promocion={promocionSeleccionada}
                telefonoWhatsApp={telefonoWhatsApp}
            />
        </div>
    );
};

export default Promociones;
