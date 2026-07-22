import { Link } from '@inertiajs/react';
import { Share, Heart, Maximize, Star, MapPin } from 'lucide-react';
import { Button } from '@/modules/shared/ui/button';

interface Room {
    id: number;
    name: string;
    type: string;
    description: string;
    price: number;
    originalPrice: number;
    guests: number;
    beds: string;
    size: string;
    view: string;
    images: string[];
}

interface RoomDetailHeroProps {
    room: Room;
}

export default function RoomDetailHero({ room }: RoomDetailHeroProps) {
    const discount = Math.round(
        ((room.originalPrice - room.price) / room.originalPrice) * 100,
    );

    return (
        <section className="bg-white dark:bg-gray-950">
            <div className="container mx-auto px-4 pt-6 pb-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between gap-4">
                    <div className="no-scrollbar flex items-center gap-2 overflow-x-auto py-1">
                        <Link
                            href="/"
                            className="transition-airbnb text-xs font-bold tracking-widest text-gray-400 uppercase hover:text-black dark:hover:text-white"
                        >
                            Inicio
                        </Link>
                        <span className="text-gray-300">/</span>
                        <Link
                            href="/habitaciones"
                            className="transition-airbnb text-xs font-bold tracking-widest text-gray-400 uppercase hover:text-black dark:hover:text-white"
                        >
                            Habitaciones
                        </Link>
                        <span className="text-gray-300">/</span>
                        <span className="truncate text-xs font-bold tracking-widest text-primary uppercase">
                            {room.name}
                        </span>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="transition-airbnb gap-2 rounded-xl bg-gray-50 text-xs font-bold tracking-widest uppercase hover:bg-black hover:text-white dark:bg-gray-900 dark:hover:bg-white dark:hover:text-black"
                        >
                            <Share className="h-4 w-4" />
                            <span className="hidden sm:inline">Compartir</span>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="transition-airbnb gap-2 rounded-xl bg-gray-50 text-xs font-bold tracking-widest uppercase hover:bg-primary hover:text-primary-foreground dark:bg-gray-900"
                        >
                            <Heart className="h-4 w-4" />
                            <span className="hidden sm:inline">Guardar</span>
                        </Button>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 pb-8 sm:px-6 lg:px-8">
                <div className="group shadow-airbnb relative grid h-[400px] grid-cols-4 grid-rows-2 gap-2 overflow-hidden rounded-3xl md:h-[500px] md:gap-3 lg:h-[600px]">
                    <div className="relative col-span-4 row-span-2 overflow-hidden lg:col-span-2">
                        <img
                            src={room.images[0] || '/placeholder.svg'}
                            alt={room.name}
                            className="transition-airbnb absolute inset-0 h-full w-full object-cover hover:scale-105"
                        />
                    </div>

                    {room.images.slice(1, 5).map((image, idx) => (
                        <div
                            key={idx}
                            className="relative hidden overflow-hidden lg:block"
                        >
                            <img
                                src={image || '/placeholder.svg'}
                                alt={`${room.name} vista ${idx + 2}`}
                                className="transition-airbnb absolute inset-0 h-full w-full object-cover hover:scale-110"
                            />
                        </div>
                    ))}

                    <button className="shadow-airbnb transition-airbnb absolute right-6 bottom-6 z-10 flex items-center gap-2 rounded-xl border border-black/10 bg-white px-6 py-2.5 text-xs font-bold tracking-widest uppercase hover:scale-105 active:scale-95 dark:border-white/10 dark:bg-gray-900">
                        <Maximize className="h-4 w-4" />
                        Ver todas las fotos
                    </button>
                </div>
            </div>

            <div className="container mx-auto border-b border-gray-100 px-4 pb-10 sm:px-6 lg:px-8 dark:border-gray-800">
                <div className="max-w-4xl">
                    <div className="mb-4 flex items-center gap-3">
                        <span className="rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[10px] font-black tracking-widest text-primary uppercase">
                            {room.type}
                        </span>
                        {discount > 0 && (
                            <span className="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black tracking-widest text-emerald-600 uppercase dark:border-emerald-800 dark:bg-emerald-900/40">
                                OFERTA -{discount}%
                            </span>
                        )}
                    </div>

                    <h1 className="mb-4 text-3xl leading-tight font-black tracking-tighter text-gray-900 md:text-5xl lg:text-6xl dark:text-white">
                        {room.name}
                    </h1>

                    <div className="flex flex-wrap items-center gap-6 text-sm font-medium text-gray-500 dark:text-gray-400">
                        <div className="flex items-center gap-1.5 text-black dark:text-white">
                            <Star className="h-4 w-4 fill-primary text-primary" />
                            <span className="font-black">4.92</span>
                            <span>•</span>
                            <span className="transition-airbnb cursor-pointer underline decoration-2 underline-offset-4 hover:text-primary">
                                127 reseñas
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5">
                            <MapPin className="h-4 w-4" />
                            <span className="transition-airbnb cursor-pointer underline decoration-2 underline-offset-4 hover:text-primary">
                                Estelí, Nicaragua
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
