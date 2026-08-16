import { GaleriaDetalleHero } from '@/modulos/compartido/componentes/GaleriaDetalleHero';
import { GrillaItemsSimilares } from '@/modulos/compartido/componentes/GrillaItemsSimilares';
import { NavegacionMigasPan } from '@/modulos/compartido/componentes/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modulos/compartido/componentes/SeccionPoliticasCondiciones';
import { SeccionServiciosIncluidos } from '@/modulos/compartido/componentes/SeccionServiciosIncluidos';
import { TarjetaFlotanteReserva } from '@/modulos/compartido/componentes/TarjetaFlotanteReserva';
import type {
    ItemHabitacion,
    HabitacionSimilares,
} from '@/modulos/compartido/types';
import { EquipamientoHabitacion } from './secciones/EquipamientoHabitacion';
import { EspecificacionesHabitacion } from './secciones/EspecificacionesHabitacion';

interface PropiedadesSeccionDetalleHabitacion {
    room: ItemHabitacion & {
        imagenes: string[];
    };
    similarRooms?: HabitacionSimilares[];
}

export const SeccionDetalleHabitacion = ({
    room,
    similarRooms = [],
}: PropiedadesSeccionDetalleHabitacion) => {
    const imagenes =
        room?.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : ['/images/main-room.webp'];

    const serviciosIncluidos = room?.serviciosIncluidos || [];
    const politicas = room?.politicas || [];
    const equipamiento = room?.equipamiento || [];
    const vistas = room?.vistas || [];

    return (
        <div className="min-h-screen w-full max-w-full overflow-x-hidden bg-background font-sans">
            {/* Migas de Pan / Breadcrumbs */}
            <NavegacionMigasPan
                migas={[
                    { label: 'Habitaciones', href: '/habitaciones' },
                    { label: room?.categoria || 'Habitación' },
                    { label: room?.nombre || '' },
                ]}
            />

            {/* Hero Principal con Galería y Especificaciones */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Columna Izquierda: Galería Hero */}
                        <div className="lg:col-span-7">
                            <GaleriaDetalleHero
                                imagenes={imagenes}
                                nombre={room?.nombre || 'Habitación'}
                                codigo={room?.codigo}
                                categoria={room?.categoria}
                            />
                        </div>

                        {/* Columna Derecha: Especificaciones y Tarjeta Flotante */}
                        <div className="space-y-6 lg:col-span-5">
                            <EspecificacionesHabitacion
                                capacidad={room?.capacidad}
                                categoria={room?.categoria}
                                medidas={room?.medidas}
                                vistas={vistas}
                            />

                            <TarjetaFlotanteReserva
                                nombreItem={room?.nombre || 'Habitación'}
                                codigoItem={room?.codigo}
                                tipoItem="habitacion"
                                precioPrincipal={room?.precio}
                                moneda={room?.moneda || '$'}
                                tipoTarifaLabel="/ noche"
                                rutaReserva={`/habitaciones/${room.slug}/reservar`}
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Servicios Incluidos */}
            <SeccionServiciosIncluidos
                servicios={serviciosIncluidos}
                titulo="Servicios Incluidos &"
                subtitulo="Amenidades asignadas a esta habitación"
            />

            {/* Equipamiento Fijo */}
            <EquipamientoHabitacion equipamiento={equipamiento} />

            {/* Políticas de la Habitación */}
            <SeccionPoliticasCondiciones
                politicas={politicas}
                titulo="Políticas de la"
                subtitulo="Regulaciones vigentes para esta habitación"
            />

            {/* Habitaciones Similares */}
            {similarRooms.length > 0 && (
                <GrillaItemsSimilares
                    items={similarRooms.map((r) => ({
                        id: r.id,
                        slug: r.slug,
                        nombre: r.nombre,
                        precio: r.precio,
                        moneda: r.moneda || '$',
                        imagen: r.imagen,
                    }))}
                    baseRoute="/habitaciones"
                    titulo="Otras Habitaciones"
                    tituloEnfasis="Disponibles"
                />
            )}
        </div>
    );
};

export default SeccionDetalleHabitacion;
