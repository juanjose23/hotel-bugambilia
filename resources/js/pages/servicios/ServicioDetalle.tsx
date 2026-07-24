import { GaleriaDetalleHero } from '@/modules/shared/components/GaleriaDetalleHero';
import { NavegacionMigasPan } from '@/modules/shared/components/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modules/shared/components/SeccionPoliticasCondiciones';
import { TarjetaFlotanteReserva } from '@/modules/shared/components/TarjetaFlotanteReserva';
import type { ItemServicio } from '@/modules/shared/types';

interface ServicioDetalleProps {
    service: ItemServicio & {
        imagenes: string[];
    };
}

const PaginaServicioDetalle = ({ service }: ServicioDetalleProps) => {
    const imagenes =
        service.imagenes && service.imagenes.length > 0
            ? service.imagenes
            : ['/images/terrace.webp'];

    return (
        <>
            {/* Cabecera / Migas de Pan Reutilizables */}
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
                        {/* Columna Izquierda: Galería Hero Reutilizable y Detalles */}
                        <div className="space-y-6 lg:col-span-7">
                            <GaleriaDetalleHero
                                imagenes={imagenes}
                                nombre={service.nombre}
                                codigo={service.codigo}
                                categoria={service.categoria}
                            />

                            {service.descripcion && (
                                <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs md:p-8">
                                    <h3 className="mb-2 text-xs font-black tracking-widest text-muted-foreground uppercase">
                                        Descripción del Servicio
                                    </h3>
                                    <p className="text-sm leading-relaxed font-medium text-muted-foreground md:text-base">
                                        {service.descripcion}
                                    </p>
                                </div>
                            )}
                        </div>

                        {/* Columna Derecha: Tarjeta Flotante Reutilizable */}
                        <div className="space-y-6 lg:col-span-5">
                            <TarjetaFlotanteReserva
                                nombreItem={service.nombre}
                                codigoItem={service.codigo}
                                tipoItem="servicio"
                                precioPrincipal={
                                    service.precio !== null &&
                                    service.precio !== undefined
                                        ? Number(service.precio)
                                        : 0
                                }
                                moneda={service.moneda || '$'}
                                tipoTarifaLabel="/ tarifa sugerida"
                                reservable={false}
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Sección de Políticas Reutilizable */}
            <SeccionPoliticasCondiciones
                politicas={service.politicas}
                titulo="Políticas del"
                subtitulo="Normativa aplicable para la contratación de este servicio"
            />
        </>
    );
};

export default PaginaServicioDetalle;
