import { Users, BedDouble, Maximize, Eye, Box } from 'lucide-react';
import { GaleriaDetalleHero } from '@/modules/shared/components/GaleriaDetalleHero';
import { GrillaItemsSimilares } from '@/modules/shared/components/GrillaItemsSimilares';
import { NavegacionMigasPan } from '@/modules/shared/components/NavegacionMigasPan';
import { SeccionPoliticasCondiciones } from '@/modules/shared/components/SeccionPoliticasCondiciones';
import { SeccionServiciosIncluidos } from '@/modules/shared/components/SeccionServiciosIncluidos';
import { TarjetaFlotanteReserva } from '@/modules/shared/components/TarjetaFlotanteReserva';
import type {
    ItemHabitacion,
    HabitacionSimilares,
} from '@/modules/shared/types';

interface HabitacionDetalleProps {
    room: ItemHabitacion & {
        imagenes: string[];
    };
    similarRooms?: HabitacionSimilares[];
}

const HabitacionDetalle = ({
    room,
    similarRooms = [],
}: HabitacionDetalleProps) => {
    const imagenes =
        room?.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : ['/images/main-room.webp'];
    const serviciosIncluidos = room?.serviciosIncluidos || [];
    const politicas = room?.politicas || [];
    const equipamiento = room?.equipamiento || [];
    const vistas = room?.vistas || [];

    return (
        <>
            {/* Migas de Pan / Breadcrumbs */}
            <NavegacionMigasPan
                migas={[
                    { label: 'Habitaciones', href: '/habitaciones' },
                    { label: room?.categoria || 'Habitación' },
                    { label: room?.nombre || '' },
                ]}
            />

            {/* Hero Principal con Galería */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Componente Reutilizable de Galería Hero */}
                        <div className="lg:col-span-7">
                            <GaleriaDetalleHero
                                imagenes={imagenes}
                                nombre={room?.nombre || 'Habitación'}
                                codigo={room?.codigo}
                                categoria={room?.categoria}
                            />
                        </div>

                        {/* Columna Derecha: Tarjeta Flotante y Especificaciones */}
                        <div className="space-y-6 lg:col-span-5">
                            {/* Grid de Especificaciones Técnicas */}
                            <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                                <div className="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <div className="flex flex-col justify-center rounded-2xl border border-border/80 bg-background p-3.5">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Capacidad
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <Users className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.capacidad || 2} personas
                                        </span>
                                    </div>

                                    <div className="flex flex-col justify-center rounded-2xl border border-border/80 bg-background p-3.5">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Camas
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <BedDouble className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.camas || '1 Cama King'}
                                        </span>
                                    </div>

                                    <div className="col-span-2 flex flex-col justify-center rounded-2xl border border-border/80 bg-background p-3.5 sm:col-span-1">
                                        <span className="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Superficie
                                        </span>
                                        <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground">
                                            <Maximize className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            {room?.medidas || '32 m²'}
                                        </span>
                                    </div>
                                </div>

                                {/* Vistas Disponibles */}
                                {vistas.length > 0 && (
                                    <div className="flex flex-wrap items-center gap-2 pt-2">
                                        <span className="text-xs font-bold text-muted-foreground">
                                            Vistas:
                                        </span>
                                        {vistas.map((vista, idx) => (
                                            <span
                                                key={idx}
                                                className="inline-flex items-center gap-1 rounded-full border border-border/80 bg-background px-3 py-1 text-xs font-extrabold text-foreground"
                                            >
                                                <Eye className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                                {vista}
                                            </span>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Tarjeta Flotante Reutilizable de Reserva */}
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
            {equipamiento.length > 0 && (
                <section className="border-t border-border/40 bg-card/40 py-10 font-sans">
                    <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <h3 className="mb-4 flex items-center gap-2 text-lg font-extrabold tracking-tight text-foreground">
                            <Box className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            Equipamiento de la Habitación
                        </h3>

                        <div className="flex flex-wrap gap-2">
                            {equipamiento.map((item, idx) => (
                                <span
                                    key={idx}
                                    className="rounded-full border border-border/80 bg-card px-3.5 py-1.5 text-xs font-semibold text-foreground"
                                >
                                    {item}
                                </span>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Políticas de la Habitación */}
            <SeccionPoliticasCondiciones
                politicas={politicas}
                titulo="Políticas de la"
                subtitulo="Regulaciones vigentes para esta habitación"
            />

            {/* Grilla de Habitaciones Similares Reutilizable */}
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
        </>
    );
};

export default HabitacionDetalle;
