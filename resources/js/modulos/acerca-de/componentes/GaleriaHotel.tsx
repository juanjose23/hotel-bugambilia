import { usePage } from '@inertiajs/react';
import { BadgeCheck } from 'lucide-react';
import { VisorGaleriaModal } from '@/modulos/compartido/componentes/VisorGaleriaModal';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { useGaleriaAcercaDe } from '../hooks/useGaleriaAcercaDe';
import type { PropiedadesGaleriaHotel } from '../interfaces/acercaDeInterfaces';
import { FiltroCategoriasGaleria } from './secciones/FiltroCategoriasGaleria';
import { TarjetaGaleriaItem } from './secciones/TarjetaGaleriaItem';

export default function GaleriaHotel({ items }: PropiedadesGaleriaHotel) {
    const pageProps = usePage().props;
    const hotelName =
        (pageProps.hotel as { name?: string })?.name || 'Hotel Bugambilias';

    const {
        categorias,
        categoriaActiva,
        setCategoriaActiva,
        itemsFiltrados,
        imagenesUrls,
        indiceImagenActiva,
        abrirVisor,
        cerrarVisor,
    } = useGaleriaAcercaDe(items);

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mx-auto mb-12 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <BadgeCheck
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Galería Fotográfica
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Explore nuestras{' '}
                        <span className="text-bugambilia-600 dark:text-bugambilia-400">
                            instalaciones
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Descubra la belleza natural, la arquitectura colonial y
                        la calidez de {hotelName}.
                    </p>
                </div>

                {/* Filtros por Categoría */}
                <FiltroCategoriasGaleria
                    categorias={categorias}
                    categoriaActiva={categoriaActiva}
                    onSeleccionarCategoria={setCategoriaActiva}
                />

                {/* Grilla de Galería */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {itemsFiltrados.map((item, index) => (
                        <TarjetaGaleriaItem
                            key={item.id}
                            item={item}
                            index={index}
                            onAbrirVisor={abrirVisor}
                        />
                    ))}
                </div>
            </div>

            {/* Modal de Visor de Galería */}
            <VisorGaleriaModal
                estaAbierto={indiceImagenActiva !== null}
                alCerrar={cerrarVisor}
                imagenes={imagenesUrls}
                indiceImagenActiva={indiceImagenActiva ?? 0}
                alSeleccionarImagen={abrirVisor}
            />
        </section>
    );
}
