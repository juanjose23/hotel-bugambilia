import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Sparkles,
    BedDouble,
    Hotel,
    Crown,
    Home,
} from 'lucide-react';
import { useState } from 'react';
import TarjetaHabitacion from '@/modules/rooms/components/TarjetaHabitacion';
import type { HabitacionGrupo } from '@/modules/rooms/components/TarjetaHabitacion';

interface HabitacionesDestacadasProps {
    rooms?: HabitacionGrupo[];
    categories?: string[];
}

const categoryIcons: Record<
    string,
    React.ComponentType<{
        className?: string;
    }>
> = {
    Todas: Hotel,
    Boutique: BedDouble,
    Deluxe: Crown,
    Suite: Home,
};

const HabitacionesDestacadas = ({
    rooms = [],
    categories = [],
}: HabitacionesDestacadasProps) => {
    const [selectedCat, setSelectedCat] = useState('Todas');

    const availableCategories = [
        'Todas',
        ...Array.from(
            new Set(
                categories.length
                    ? categories
                    : rooms
                          .map((r) => r.categoria)
                          .filter((c): c is string => typeof c === 'string'),
            ),
        ),
    ];

    const filteredRooms =
        selectedCat === 'Todas'
            ? rooms
            : rooms.filter(
                  (r) =>
                      r.categoria &&
                      r.categoria
                          .toLowerCase()
                          .includes(selectedCat.toLowerCase()),
              );

    if (rooms.length === 0) {
        return null;
    }

    return (
        <section className="overflow-hidden border-b border-border/40 bg-background py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Section Header */}
                <div className="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <div className="max-w-2xl">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                            <Sparkles className="h-3.5 w-3.5" />
                            Catálogo de Habitaciones
                        </div>
                        <h2 className="text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Habitaciones{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Exclusivas
                            </span>
                        </h2>
                        <p className="mt-2 text-base font-medium text-muted-foreground sm:text-lg">
                            Descubra estancias diseñadas para el máximo
                            descanso, confort boutique y privacidad.
                        </p>
                    </div>

                    <Link
                        href="/habitaciones"
                        className="inline-flex items-center gap-2 border-b-2 border-bugambilia-600 pb-1 text-xs font-black tracking-widest text-bugambilia-600 uppercase transition-all duration-300 hover:gap-3 dark:border-bugambilia-400 dark:text-bugambilia-400"
                    >
                        <span>Explorar catálogo completo</span>
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>

                {/* Category Pills (Airbnb Style Horizontal Scrolling Tabs) */}
                {availableCategories.length > 1 && (
                    <div className="no-scrollbar mb-8 flex items-center gap-2 overflow-x-auto border-b border-border/40 pb-6">
                        {availableCategories.map((cat) => {
                            const Icon = categoryIcons[cat] || Hotel;
                            const isSelected = selectedCat === cat;

                            return (
                                <button
                                    key={cat}
                                    onClick={() => setSelectedCat(cat)}
                                    className={`flex shrink-0 cursor-pointer items-center gap-2 rounded-full px-4 py-2.5 text-xs font-bold transition-all duration-300 ${
                                        isSelected
                                            ? 'shadow-airbnb bg-foreground text-background'
                                            : 'border border-border/80 bg-card text-muted-foreground hover:border-gray-400 hover:text-foreground dark:hover:border-gray-600'
                                    }`}
                                >
                                    <Icon
                                        className={`h-3.5 w-3.5 ${isSelected ? 'text-background' : 'text-bugambilia-500'}`}
                                    />
                                    <span>{cat}</span>
                                </button>
                            );
                        })}
                    </div>
                )}

                {/* Grid/Scrollable of Rooms */}
                <div className="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto pb-4 sm:grid sm:grid-cols-2 sm:overflow-x-visible sm:pb-0 lg:grid-cols-3">
                    {filteredRooms.map((room) => (
                        <div
                            key={room.id}
                            className="w-[85vw] shrink-0 snap-center sm:w-auto"
                        >
                            <TarjetaHabitacion habitacion={room} />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};
export default HabitacionesDestacadas;
