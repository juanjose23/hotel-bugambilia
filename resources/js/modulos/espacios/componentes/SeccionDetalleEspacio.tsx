import { GrillaItemsSimilares } from '@/modulos/compartido/componentes/GrillaItemsSimilares';
import { NavegacionMigasPan } from '@/modulos/compartido/componentes/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modulos/compartido/componentes/SeccionPoliticasCondiciones';
import { SeccionServiciosIncluidos } from '@/modulos/compartido/componentes/SeccionServiciosIncluidos';
import { TarjetaFlotanteReserva } from '@/modulos/compartido/componentes/TarjetaFlotanteReserva';
import type { PropiedadesSeccionDetalleEspacio } from '../interfaces/espacioInterfaces';
import { BloquesCaracteristicasEspacio } from './secciones/BloquesCaracteristicasEspacio';
import { CabeceraDetalleEspacio } from './secciones/CabeceraDetalleEspacio';
import { EquipamientoEspacio } from './secciones/EquipamientoEspacio';
import { EsquemasMontajeEspacio } from './secciones/EsquemasMontajeEspacio';
import { ListadoSubEspacios } from './secciones/ListadoSubEspacios';
import { MosaicoGaleriaEspacio } from './secciones/MosaicoGaleriaEspacio';

export const SeccionDetalleEspacio = ({
    space,
    similarSpaces = [],
}: PropiedadesSeccionDetalleEspacio) => {
    const imagenes =
        space?.imagenes && space.imagenes.length > 0
            ? space.imagenes
            : ['/images/terrace.webp'];

    const serviciosIncluidos = space?.serviciosIncluidos || [];
    const politicas = space?.politicas || [];
    const metaDatos = space?.meta_datos;
    const equipamiento = Array.isArray(metaDatos?.equipamiento_incluido)
        ? (metaDatos.equipamiento_incluido as string[])
        : [];
    const subEspacios = space?.sub_espacios || [];
    const esRestaurante =
        space?.es_restaurante ||
        space?.tipo?.toLowerCase().includes('restaurante');

    return (
        <section className="min-h-screen w-full max-w-full overflow-x-hidden bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Migas de Pan */}
                <div className="mb-4">
                    <NavegacionMigasPan
                        migas={[
                            {
                                label: 'Espacios & Ambientes',
                                href: '/espacios',
                            },
                            {
                                label:
                                    space?.tipo_label ||
                                    space?.tipo ||
                                    'Ambiente',
                            },
                            { label: space?.nombre || '' },
                        ]}
                    />
                </div>

                {/* Cabecera Principal del Espacio */}
                <div className="mb-6">
                    <CabeceraDetalleEspacio
                        nombre={space?.nombre || ''}
                        descripcion={space?.descripcion}
                        tipoLabel={space?.tipo_label}
                        tipo={space?.tipo}
                        ubicacion={space?.ubicacion}
                        capacidad={space?.capacidad}
                        metrosCuadrados={metaDatos?.metros_cuadrados}
                    />
                </div>

                {/* Mosaico Fotográfico Bento Box (Estilo Airbnb Luxe) */}
                <div className="mb-10">
                    <MosaicoGaleriaEspacio
                        imagenes={imagenes}
                        nombre={space?.nombre || 'Espacio Boutique'}
                        tipoLabel={space?.tipo_label || space?.tipo}
                    />
                </div>

                {/* Grid Principal Layout: Detalles a la Izquierda, Tarjeta Reserva Flotante a la Derecha */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div className="flex flex-col gap-8 lg:col-span-2">
                        {/* Bloques de Características Rápidas */}
                        <BloquesCaracteristicasEspacio />

                        {/* Esquemas de Montaje & Distribución de Eventos */}
                        <EsquemasMontajeEspacio
                            capacidadMaxima={space?.capacidad || 50}
                            capacidadMesas={metaDatos?.capacidad_mesas}
                        />

                        {/* Sub-Espacios Integrados */}
                        <ListadoSubEspacios subEspacios={subEspacios} />

                        {/* Equipamiento Audiovisual e Infraestructura */}
                        <EquipamientoEspacio equipamiento={equipamiento} />

                        {/* Servicios Incluidos */}
                        <SeccionServiciosIncluidos
                            servicios={serviciosIncluidos}
                        />

                        {/* Políticas de Uso */}
                        <SeccionPoliticasCondiciones politicas={politicas} />
                    </div>

                    {/* Columna Derecha: Tarjeta Flotante de Reserva */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-24">
                            <TarjetaFlotanteReserva
                                nombreItem={space?.nombre || 'Espacio'}
                                tipoItem="espacio"
                                precio={
                                    Number(
                                        space?.precio ?? space?.precio_base,
                                    ) || 0
                                }
                                moneda={space?.moneda || '$'}
                                rutaReserva={
                                    esRestaurante
                                        ? '/restaurante'
                                        : space?.slug
                                          ? `/espacios/${space.slug}/reservar`
                                          : `/espacios/${space.id}/reservar`
                                }
                                labelBoton={
                                    esRestaurante
                                        ? 'Reservar Mesa en Restaurante'
                                        : 'Solicitar Reserva de Espacio'
                                }
                                reservable={space?.reservable ?? true}
                            />
                        </div>
                    </div>
                </div>

                {/* Espacios Similares Recomendados */}
                {similarSpaces.length > 0 && (
                    <div className="mt-16 border-t border-border/50 pt-12">
                        <h2 className="mb-6 text-2xl font-black text-foreground">
                            Otros Salones & Espacios Recomendados
                        </h2>
                        <GrillaItemsSimilares
                            baseRoute="/espacios"
                            items={similarSpaces.map((item) => ({
                                id: item.id,
                                nombre: item.nombre,
                                slug: item.slug,
                                tipo: item.tipo,
                                precio: item.precio,
                                moneda: item.moneda,
                                imagen: item.imagen,
                            }))}
                        />
                    </div>
                )}
            </div>
        </section>
    );
};

export default SeccionDetalleEspacio;
