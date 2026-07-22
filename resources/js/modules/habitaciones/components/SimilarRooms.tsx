import { Link } from '@inertiajs/react';
import { Star, Users, Bed, ArrowRight } from 'lucide-react';
import { Button } from '@/modules/shared/ui/button';

const allRooms = [
    {
        id: 1,
        name: 'Habitación Doble Estándar',
        price: 350,
        originalPrice: 389,
        guests: 2,
        beds: '1 cama doble',
        image: '/images/main-room.jpg',
        rating: 4.8,
        reviews: 89,
    },
    {
        id: 2,
        name: 'Habitación Doble Deluxe',
        price: 390,
        originalPrice: 433,
        guests: 2,
        beds: '1 cama king',
        image: '/images/group-room.jpg',
        rating: 4.9,
        reviews: 127,
    },
    {
        id: 4,
        name: 'Junior Suite',
        price: 590,
        originalPrice: 656,
        guests: 4,
        beds: '1 king + sofá',
        image: '/images/room-detail.jpg',
        rating: 4.9,
        reviews: 156,
    },
    {
        id: 6,
        name: 'Master Suite',
        price: 890,
        originalPrice: 989,
        guests: 6,
        beds: '2 recámaras',
        image: '/images/terrace.jpg',
        rating: 5.0,
        reviews: 78,
    },
];

interface SimilarRoomsProps {
    currentRoomId: number;
}

export default function SimilarRooms({ currentRoomId }: SimilarRoomsProps) {
    const similarRooms = allRooms
        .filter((room) => room.id !== currentRoomId)
        .slice(0, 3);

    return (
        <section className="border-t border-gray-100 bg-gray-50/50 py-24 dark:border-gray-900 dark:bg-gray-900/30">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 flex items-end justify-between">
                    <div>
                        <h2 className="mb-3 text-[10px] font-black tracking-[0.2em] text-bugambilia-600 uppercase">
                            Continuar Explorando
                        </h2>
                        <h3 className="text-3xl font-black tracking-tighter text-gray-900 md:text-4xl dark:text-white">
                            Otras estancias boutique
                        </h3>
                    </div>
                    <Link
                        href="/habitaciones"
                        className="group transition-airbnb hidden items-center gap-2 text-xs font-black tracking-widest text-gray-400 uppercase hover:text-black sm:flex dark:hover:text-white"
                    >
                        Ver catálogo completo
                        <ArrowRight className="transition-airbnb h-4 w-4 group-hover:translate-x-1" />
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {similarRooms.map((room) => (
                        <Link
                            key={room.id}
                            href={`/habitaciones/${room.id}`}
                            className="group block"
                        >
                            <div className="shadow-airbnb group-hover:shadow-airbnb-hover transition-airbnb relative mb-5 aspect-[4/3] overflow-hidden rounded-3xl bg-gray-100 dark:bg-gray-800">
                                <img
                                    src={room.image || '/placeholder.svg'}
                                    alt={room.name}
                                    className="transition-airbnb absolute inset-0 h-full w-full object-cover group-hover:scale-105"
                                />
                                <div className="absolute top-4 right-4 flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 shadow-sm backdrop-blur-md dark:bg-black/40">
                                    <Star className="h-3 w-3 fill-bugambilia-600 text-bugambilia-600" />
                                    <span className="text-[11px] font-black text-black dark:text-white">
                                        {room.rating}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <div className="mb-1 flex items-start justify-between">
                                    <h4 className="transition-airbnb text-lg font-black tracking-tight text-gray-900 group-hover:text-bugambilia-600 dark:text-white">
                                        {room.name}
                                    </h4>
                                </div>
                                <div className="mb-3 flex items-center gap-3 text-xs font-medium text-gray-500">
                                    <span className="flex items-center gap-1">
                                        <Users className="h-3 w-3" />{' '}
                                        {room.guests} pers.
                                    </span>
                                    <span>&bull;</span>
                                    <span className="flex items-center gap-1">
                                        <Bed className="h-3 w-3" /> {room.beds}
                                    </span>
                                </div>
                                <div className="flex items-baseline gap-1.5">
                                    <span className="text-lg font-black text-black dark:text-white">
                                        ${room.price}
                                    </span>
                                    <span className="text-xs font-medium text-gray-400">
                                        noche
                                    </span>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>

                <div className="mt-10 sm:hidden">
                    <Button
                        variant="outline"
                        className="w-full rounded-2xl border-gray-200 py-6 text-[10px] font-black tracking-widest uppercase"
                        asChild
                    >
                        <Link href="/habitaciones">
                            Ver todas las habitaciones
                        </Link>
                    </Button>
                </div>
            </div>
        </section>
    );
}
