import { Head } from '@inertiajs/react';
import SeccionHabitaciones from '@/modules/rooms/components/SeccionHabitaciones';
import type { RoomItem } from '@/modules/rooms/components/TarjetaHabitacion';
import type { DatosPaginacion } from '@/modules/shared/types';
interface HabitacionesProps {
    rooms?: RoomItem[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}
const Habitaciones = ({
    rooms = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: HabitacionesProps) => {
    return (
        <>
            <Head>
                <title>Habitaciones — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Habitaciones y suites de lujo en Hotel Bugambilias Estelí — WiFi, estacionamiento y desayuno incluido. Reserva en línea."
                />
            </Head>
            <SeccionHabitaciones
                rooms={rooms}
                categorias={categorias}
                selectedCategory={selectedCategory}
                searchQuery={searchQuery}
                pagination={pagination}
            />
        </>
    );
};
export default Habitaciones;
