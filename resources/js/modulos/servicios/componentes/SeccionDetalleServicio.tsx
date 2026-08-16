import { GaleriaDetalleHero } from '@/modulos/compartido/componentes/GaleriaDetalleHero';
import { NavegacionMigasPan } from '@/modulos/compartido/componentes/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modulos/compartido/componentes/SeccionPoliticasCondiciones';
import { TarjetaFlotanteReserva } from '@/modulos/compartido/componentes/TarjetaFlotanteReserva';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import type { PropiedadesSeccionDetalleServicio } from '../interfaces/servicioInterfaces';

export const SeccionDetalleServicio = ({
    service,
}: PropiedadesSeccionDetalleServicio) => {
    const imagenes =
        service.imagenes && service.imagenes.length > 0
            ? service.imagenes
            : ['/images/terrace.webp'];

    const precioPrincipal =
        service.precio !== null && service.precio !== undefined
            ? Number(service.precio)
            : 0;

    return (
        <div className="min-h-screen w-full max-w-full overflow-x-hidden bg-background font-sans">
            {/* Cabecera / Migas de Pan */}
            <NavegacionMigasPan
                migas={[
                    { label: 'Servicios', href: '/servicios' },
                    { label: service.categoria || 'Servicio Exclusivo' },
                    { label: service.nombre },
                ]}
                badge={service.categoria || 'Servicio Exclusivo'}
            />

            {/* Contenido Principal */}
            <section className="py-10 font-sans md:py-16">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-10 lg:grid-cols-12 lg:gap-12">
                        {/* Columna Izquierda: Galería Hero y Descripción */}
                        <div className="space-y-6 lg:col-span-7">
                            <GaleriaDetalleHero
                                imagenes={imagenes}
                                nombre={service.nombre}
                                codigo={service.codigo}
                                categoria={service.categoria}
                            />

                            {service.descripcion && (
                                <Card className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs md:p-8">
                                    <h3 className="mb-2 text-xs font-black tracking-widest text-muted-foreground uppercase">
                                        Descripción del Servicio
                                    </h3>
                                    <p className="text-sm leading-relaxed font-medium text-muted-foreground md:text-base">
                                        {service.descripcion}
                                    </p>
                                </Card>
                            )}
                        </div>

                        {/* Columna Derecha: Tarjeta Flotante */}
                        <div className="space-y-6 lg:col-span-5">
                            <TarjetaFlotanteReserva
                                nombreItem={service.nombre}
                                codigoItem={service.codigo}
                                tipoItem="servicio"
                                precioPrincipal={precioPrincipal}
                                moneda={service.moneda || '$'}
                                tipoTarifaLabel="/ tarifa sugerida"
                                reservable={false}
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Sección de Políticas */}
            <SeccionPoliticasCondiciones
                politicas={service.politicas}
                titulo="Políticas del"
                subtitulo="Normativa aplicable para la contratación de este servicio"
            />
        </div>
    );
};

export default SeccionDetalleServicio;
