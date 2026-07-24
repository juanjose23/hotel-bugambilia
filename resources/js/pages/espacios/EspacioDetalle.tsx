import { Users, MapPin } from 'lucide-react';
import { GaleriaDetalleHero } from '@/modules/shared/components/GaleriaDetalleHero';
import { GrillaItemsSimilares } from '@/modules/shared/components/GrillaItemsSimilares';
import { NavegacionMigasPan } from '@/modules/shared/components/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modules/shared/components/SeccionPoliticasCondiciones';
import { SeccionServiciosIncluidos } from '@/modules/shared/components/SeccionServiciosIncluidos';
import { TarjetaFlotanteReserva } from '@/modules/shared/components/TarjetaFlotanteReserva';
import { Badge } from '@/modules/shared/ui/insignia';

interface EspacioItem {
    id: number;
    codigo: string;
    slug?: string;
    nombre: string;
    tipo: string;
    tipo_label: string;
    descripcion: string;
    ubicacion: string;
    precio: number;
    precio_por_hora?: number;
    precio_base?: number;
    es_oferta?: boolean;
    tipo_tarifa_label?: string;
    moneda: string;
    capacidad: number;
    web: boolean;
    reservable: boolean;
    es_restaurante: boolean;
    imagenes: string[];
    meta_datos?: Record<string, string | number | boolean | null>;
    serviciosIncluidos?: Array<
        | string
        | {
              nombre: string;
              descripcion?: string | null;
              icono?: string | null;
              incluido?: boolean | null;
          }
    >;
    politicas?: Array<{
        id?: number;
        nombre: string;
        descripcion: string;
    }>;
}

interface SimilarSpace {
    id: number;
    slug?: string;
    nombre: string;
    tipo: string;
    precio: number;
    moneda: string;
    imagen: string;
}

interface EspacioDetalleProps {
    space: EspacioItem;
    similarSpaces?: SimilarSpace[];
}

const EspacioDetalle = ({ space, similarSpaces = [] }: EspacioDetalleProps) => {
    const imagenes =
        space?.imagenes && space.imagenes.length > 0
            ? space.imagenes
            : ['/images/terrace.webp'];
    const serviciosIncluidos = space?.serviciosIncluidos || [];
    const politicas = space?.politicas || [];

    return (
        <>
            {/* Componente Reutilizable de Migas de Pan */}
            <NavegacionMigasPan
                migas={[
                    { label: 'Espacios & Ambientes', href: '/espacios' },
                    { label: space?.tipo_label || 'Ambiente' },
                    { label: space?.nombre || '' },
                ]}
                badge={space?.tipo_label || 'Espacio Exclusivo'}
            />

            {/* Hero Principal */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Columna Izquierda: Galería Hero Reutilizable y Detalles */}
                        <div className="space-y-6 lg:col-span-7">
                            <GaleriaDetalleHero
                                imagenes={imagenes}
                                nombre={space?.nombre || 'Espacio'}
                                codigo={space?.codigo}
                                categoria={space?.tipo_label}
                            />

                            {/* Información y Meta datos */}
                            <div className="space-y-6">
                                <div>
                                    <div className="mb-2 flex items-center gap-3">
                                        <Badge
                                            variant="secondary"
                                            className="rounded-full px-3 py-1 text-[11px] font-extrabold uppercase"
                                        >
                                            {space?.tipo_label}
                                        </Badge>
                                        {space?.ubicacion && (
                                            <span className="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground">
                                                <MapPin className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                                {space.ubicacion}
                                            </span>
                                        )}
                                    </div>
                                    <h2 className="text-2xl font-black tracking-tight text-foreground md:text-3xl">
                                        {space?.nombre}
                                    </h2>
                                </div>

                                {space?.descripcion && (
                                    <p className="text-sm leading-relaxed font-medium text-muted-foreground md:text-base">
                                        {space.descripcion}
                                    </p>
                                )}

                                {/* Especificaciones */}
                                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div className="flex flex-col justify-center rounded-2xl border border-border/80 bg-card p-4">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Capacidad Estimada
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <Users className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            Hasta {space?.capacidad || 1}{' '}
                                            personas
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Columna Derecha: Tarjeta Flotante Reutilizable */}
                        <div className="lg:sticky lg:top-28 lg:col-span-5">
                            <TarjetaFlotanteReserva
                                nombreItem={space?.nombre || 'Espacio'}
                                codigoItem={space?.codigo}
                                tipoItem="espacio"
                                precioPrincipal={space?.precio}
                                precioPorHora={space?.precio_por_hora}
                                precioBase={space?.precio_base}
                                moneda={space?.moneda || 'C$'}
                                tipoTarifaLabel={space?.tipo_tarifa_label}
                                esOferta={space?.es_oferta}
                                reservable={space?.reservable}
                                rutaReserva={`/espacios/${space.slug || space.id}/reservar`}
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Servicios Incluidos */}
            <SeccionServiciosIncluidos
                servicios={serviciosIncluidos}
                titulo="Servicios e Instalaciones"
                subtitulo="Equipamiento y comodidades de este ambiente"
            />

            {/* Políticas del Espacio */}
            <SeccionPoliticasCondiciones
                politicas={politicas}
                titulo="Políticas de la"
                subtitulo="Normativa de uso e ingreso a las instalaciones"
            />

            {/* Grilla Reutilizable de Espacios Similares */}
            <GrillaItemsSimilares
                items={similarSpaces.map((sim) => ({
                    id: sim.id,
                    slug: sim.slug,
                    nombre: sim.nombre,
                    tipo: sim.tipo,
                    precio: sim.precio,
                    moneda: sim.moneda,
                    imagen: sim.imagen,
                }))}
                baseRoute="/espacios"
                titulo="Otros Ambientes"
                tituloEnfasis="Disponibles"
            />
        </>
    );
};

export default EspacioDetalle;
