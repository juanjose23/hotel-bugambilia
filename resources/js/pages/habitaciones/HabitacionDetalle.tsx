import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Hotel } from 'lucide-react';
import { HabitacionDetalleGaleria } from '@/modules/habitaciones/components/HabitacionDetalleGaleria';
import { HabitacionDetalleInfo } from '@/modules/habitaciones/components/HabitacionDetalleInfo';
import { HabitacionReservaCard } from '@/modules/habitaciones/components/HabitacionReservaCard';
import type { HabitacionDetalleProps } from '@/modules/habitaciones/types';
import { RoomCard } from '@/modules/shared/components/RoomCard';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const HabitacionDetalle = ({
    room,
    similarRooms = [],
    serviciosDisponibles = [],
    beneficiosCliente = [],
    diasAgotados = [],
}: HabitacionDetalleProps) => {
    const { hotel } = usePropiedadesPagina();
    const telefonoWhatsApp = (hotel?.whatsapp || '+50587136805').replace(
        /\D/g,
        '',
    );

    const imagenes =
        room?.imagenes && room.imagenes.length > 0
            ? room.imagenes
            : [room?.imagen || '/images/main-room.webp'];

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>{`${room?.nombre || 'Habitación'} — Hotel Bugambilias Estelí`}</title>
                <meta
                    name="description"
                    content={`Reserva la suite ${room?.nombre} en Hotel Bugambilias Estelí. Mejor precio garantizado, piscina y atención premium.`}
                />
            </Head>

            {/* Barra de Retorno y Navegación */}
            <div className="border-b border-border bg-card/60 py-3.5 backdrop-blur-md">
                <div className="container mx-auto flex items-center justify-between px-4 sm:px-6">
                    <Link
                        href="/habitaciones"
                        className="inline-flex items-center gap-1.5 text-xs font-black text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        <span>Volver a habitaciones</span>
                    </Link>
                    <div className="inline-flex items-center gap-1.5 text-xs font-bold text-muted-foreground">
                        <Hotel className="size-3 text-primary dark:text-rose-400" />
                        <span>{room?.categoria || 'Suite Boutique'}</span>
                    </div>
                </div>
            </div>

            {/* Contenido Principal */}
            <div className="container mx-auto px-4 py-8 sm:px-6 lg:max-w-6xl">
                {/* Título & Ubicación */}
                <div className="mb-6">
                    <div className="inline-flex items-center gap-1.5 rounded-full border border-primary/30 bg-primary/10 px-3 py-0.5 text-[11px] font-black text-primary uppercase dark:border-rose-500/40 dark:bg-rose-950/60 dark:text-rose-200">
                        <span>{room?.categoria || 'Habitación Boutique'}</span>
                    </div>
                    <h1 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-4xl">
                        {room?.nombre}
                    </h1>
                    <p className="mt-1 text-xs font-medium text-muted-foreground">
                        Salida Sur • Estelí, Nicaragua • Hotel Bugambilias
                    </p>
                </div>

                {/* Galería Mosaico / Lightbox */}
                <HabitacionDetalleGaleria
                    imagenes={imagenes}
                    nombreHabitacion={room?.nombre || 'Habitación'}
                />

                {/* Layout 2 Columnas: Detalles (Izquierda) + Card de Reserva Sticky (Derecha) */}
                <div className="mt-10 grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <HabitacionDetalleInfo room={room} />
                    </div>

                    <div className="lg:col-span-1">
                        <HabitacionReservaCard
                            room={room}
                            telefonoWhatsApp={telefonoWhatsApp}
                            diasAgotados={diasAgotados}
                            serviciosDisponibles={serviciosDisponibles}
                            beneficiosCliente={beneficiosCliente}
                        />
                    </div>
                </div>

                {/* Habitaciones Similares Recomendadas */}
                {similarRooms.length > 0 && (
                    <div className="mt-16 border-t border-border pt-12">
                        <div className="flex items-center justify-between">
                            <div>
                                <h2 className="text-xl font-black tracking-tight text-foreground sm:text-2xl">
                                    Otras suites que te pueden gustar
                                </h2>
                                <p className="mt-0.5 text-xs text-muted-foreground">
                                    Descubre más opciones de hospedaje en
                                    nuestro hotel
                                </p>
                            </div>
                            <Link
                                href="/habitaciones"
                                className="text-xs font-black text-primary hover:underline dark:text-rose-400"
                            >
                                Ver todas
                            </Link>
                        </div>

                        <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {similarRooms.slice(0, 3).map((r) => (
                                <RoomCard key={r.id} room={r} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default HabitacionDetalle;
