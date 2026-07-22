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
import RoomCard from '@/modules/habitaciones/components/RoomCard';

interface RoomData {
    id: number | string;
    codigo?: string;
    numero?: number;
    slug?: string;
    nombre?: string;
    name?: string;
    descripcion?: string;
    categoria?: string;
    precio?: number;
    price?: number;
    moneda?: string;
    capacidad?: number;
    camas?: string;
    beds?: string;
    imagen?: string;
    image?: string;
    popular?: boolean;
}

interface FeaturedRoomsProps {
    rooms?: RoomData[];
    categories?: string[];
}

const categoryIcons: Record<string, any> = {
    Todas: Hotel,
    Boutique: BedDouble,
    Deluxe: Crown,
    Suite: Home,
};

export default function FeaturedRooms({
    rooms = [],
    categories = [],
}: FeaturedRoomsProps) {
    const [selectedCat, setSelectedCat] = useState('Todas');

    const displayRooms =
        rooms.length > 0
            ? rooms.map((r, index) => ({
                  id: r.id,
                  name:
                      r.nombre ||
                      r.name ||
                      `Habitación ${r.numero ?? index + 1}`,
                  location: 'Estelí, Nicaragua',
                  price: r.precio ?? r.price ?? 45,
                  rating: 4.95 - (index % 3) * 0.04,
                  image:
                      r.imagen ||
                      r.image ||
                      'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
                  type: r.categoria || 'Habitación Boutique',
                  beds: r.camas || r.beds || `${r.capacidad ?? 2} Huéspedes`,
                  popular: index === 0 || index === 2,
              }))
            : [
                  {
                      id: 1,
                      name: 'Habitación Doble Estándar',
                      location: 'Estelí, Nicaragua',
                      price: 45,
                      rating: 4.96,
                      image: 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
                      type: 'Habitación Boutique',
                      beds: '1 Cama Matrimonial • 2 Huéspedes',
                      popular: true,
                  },
                  {
                      id: 2,
                      name: 'Habitación Doble Deluxe',
                      location: 'Estelí, Nicaragua',
                      price: 55,
                      rating: 4.91,
                      image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
                      type: 'Habitación Deluxe',
                      beds: '1 Cama King • Air Con',
                      popular: false,
                  },
                  {
                      id: 3,
                      name: 'Junior Suite Familiar',
                      location: 'Estelí, Nicaragua',
                      price: 75,
                      rating: 4.98,
                      image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                      type: 'Suite completa',
                      beds: '1 Cama King + Sofá • Vista Jardín',
                      popular: true,
                  },
              ];

    const availableCategories = [
        'Todas',
        ...Array.from(
            new Set(
                categories.length
                    ? categories
                    : displayRooms.map((r) => r.type),
            ),
        ),
    ];

    const filteredRooms =
        selectedCat === 'Todas'
            ? displayRooms
            : displayRooms.filter((r) =>
                  r.type.toLowerCase().includes(selectedCat.toLowerCase()),
              );

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

                {/* Grid of Rooms (Airbnb Style Layout) */}
                <div className="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    {(filteredRooms.length > 0
                        ? filteredRooms
                        : displayRooms
                    ).map((room) => (
                        <RoomCard key={room.id} habitacion={room} />
                    ))}
                </div>
            </div>
        </section>
    );
}
