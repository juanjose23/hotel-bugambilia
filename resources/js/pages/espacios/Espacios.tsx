import { Head } from '@inertiajs/react';
import { Building2, RotateCcw } from 'lucide-react';
import { EspacioCard } from '@/modules/espacios/components/EspacioCard';
import { EspacioFiltros } from '@/modules/espacios/components/EspacioFiltros';
import { EspacioHero } from '@/modules/espacios/components/EspacioHero';
import { useFiltrosEspacios } from '@/modules/espacios/hooks/useFiltrosEspacios';
import type { EspaciosPageProps } from '@/modules/espacios/types';
import { EspacioGridSkeleton } from '@/modules/shared/components/skeletons';
import { Button } from '@/modules/shared/components/ui/button';

export const Espacios = ({
    espacios = [],
    tipos = [],
    tipoSeleccionado = 'TODOS',
}: EspaciosPageProps) => {
    const {
        filtros,
        espaciosFiltrados,
        isPending,
        manejarCambioTipo,
        manejarCambioBusqueda,
        manejarCambioCapacidad,
        manejarReset,
    } = useFiltrosEspacios({
        espacios,
        tipoSeleccionado,
    });

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>Espacios & Salones de Eventos — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Conoce los espacios e instalaciones de Hotel Bugambilias en Estelí: salones para conferencias, eventos sociales, fitness center y áreas de relajación."
                />
            </Head>

            {/* Hero Principal */}
            <EspacioHero />

            {/* Filtros */}
            <EspacioFiltros
                tipos={tipos}
                tipoActivo={filtros.tipo}
                alSeleccionarTipo={manejarCambioTipo}
                busqueda={filtros.buscar}
                alCambiarBusqueda={manejarCambioBusqueda}
                capacidadMinima={filtros.capacidadMinima}
                alCambiarCapacidad={manejarCambioCapacidad}
                alLimpiar={manejarReset}
                totalResultados={espaciosFiltrados.length}
            />

            {/* Catálogo de Espacios */}
            <div className="container mx-auto px-4 py-12 sm:px-6">
                {isPending ? (
                    <EspacioGridSkeleton cantidad={3} />
                ) : espaciosFiltrados.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/40 py-16 text-center">
                        <Building2 className="size-12 text-muted-foreground/40" />
                        <h3 className="mt-4 text-base font-black text-foreground">
                            No se encontraron espacios con estos filtros
                        </h3>
                        <p className="mt-1 max-w-sm text-xs text-muted-foreground">
                            Prueba ajustando la capacidad mínima o seleccionando
                            otro tipo de instalación.
                        </p>
                        <Button
                            type="button"
                            onClick={manejarReset}
                            className="mt-5 cursor-pointer rounded-full bg-primary px-5 py-2 text-xs font-black text-primary-foreground shadow-sm hover:bg-primary/90"
                        >
                            <RotateCcw className="mr-1.5 size-3.5" />
                            <span>Ver todos los espacios</span>
                        </Button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {espaciosFiltrados.map((espacio) => (
                            <EspacioCard key={espacio.id} espacio={espacio} />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default Espacios;
