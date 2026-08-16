import { Head } from '@inertiajs/react';
import type {
    ItemHabitacion,
    HabitacionSimilares,
} from '@/modulos/compartido/types';
import { SeccionDetalleHabitacion } from '@/modulos/habitaciones/componentes/SeccionDetalleHabitacion';

interface PropiedadesPaginaHabitacionDetalle {
    room: ItemHabitacion & {
        imagenes: string[];
    };
    similarRooms?: HabitacionSimilares[];
}

export const PaginaHabitacionDetalle = ({
    room,
    similarRooms = [],
}: PropiedadesPaginaHabitacionDetalle) => {
    return (
        <>
            <Head>
                <title>{`${room?.nombre || 'Detalle Habitación'} — Hotel Bugambilias`}</title>
                <meta
                    name="description"
                    content={`Reserve la habitación ${room?.nombre} en Hotel Bugambilias Estelí. Confort boutique, amenidades exclusivas y mejor tarifa garantizada.`}
                />
            </Head>
            <SeccionDetalleHabitacion room={room} similarRooms={similarRooms} />
        </>
    );
};

export default PaginaHabitacionDetalle;
