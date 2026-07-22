import type { RoomItem } from '@/modules/habitaciones/components/RoomCard';
import SeccionHabitaciones from '@/modules/habitaciones/components/SeccionHabitaciones';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface HabitacionesProps {
    rooms?: RoomItem[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: any;
}

export default function Habitaciones({
    rooms = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: HabitacionesProps) {
    return (
        <LayoutPublico>
            <SeccionHabitaciones
                rooms={rooms}
                categorias={categorias}
                selectedCategory={selectedCategory}
                searchQuery={searchQuery}
                pagination={pagination}
            />
        </LayoutPublico>
    );
}
