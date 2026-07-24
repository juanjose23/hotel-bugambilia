import { Waves, UtensilsCrossed, Car, Wifi } from 'lucide-react';
import TarjetaHabitacion from '@/modules/rooms/components/TarjetaHabitacion';
import { Button } from '@/modules/shared/ui/boton';
const rooms = [
    {
        id: 1,
        name: 'Habitación Doble Estándar',
        type: 'Habitación Boutique',
        guests: '2 huéspedes',
        beds: '1 cama matrimonial',
        price: 350,
        rating: 4.9,
        image: '/images/main-room.webp',
        popular: true,
    },
    {
        id: 2,
        name: 'Habitación Doble Deluxe',
        type: 'Habitación Premium',
        guests: '2 huéspedes',
        beds: '1 cama king size',
        price: 390,
        rating: 4.8,
        image: '/images/group-room.webp',
        popular: false,
    },
    {
        id: 4,
        name: 'Junior Suite Familiar',
        type: 'Suite completa',
        guests: '4 huéspedes',
        beds: '1 king + sofá cama',
        price: 590,
        rating: 4.8,
        image: '/images/room-detail.webp',
        popular: false,
    },
    {
        id: 6,
        name: 'Master Suite Bugambilias',
        type: 'Lujo Executive',
        guests: '6 huéspedes',
        beds: '2 recámaras completas',
        price: 890,
        rating: 5.0,
        image: '/images/terrace.webp',
        popular: true,
    },
];
const CuadriculaHabitaciones = () => {
    return (
        <section className="bg-white py-20 dark:bg-gray-950">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <div className="max-w-xl">
                        <h2 className="mb-2 text-3xl font-black tracking-tighter text-gray-900 underline decoration-bugambilia-600 decoration-4 underline-offset-8 dark:text-white">
                            Nuestras Habitaciones
                        </h2>
                        <p className="font-medium text-gray-500 dark:text-gray-400">
                            Seleccionadas cuidadosamente para tu confort.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Button
                            variant="outline"
                            className="transition-airbnb rounded-xl border-gray-200 px-6 text-xs font-bold tracking-widest uppercase shadow-sm hover:bg-black hover:text-white dark:border-gray-800 dark:hover:bg-white dark:hover:text-black"
                        >
                            Filtrar por Precio
                        </Button>
                        <Button
                            variant="outline"
                            className="transition-airbnb rounded-xl border-gray-200 px-6 text-xs font-bold tracking-widest uppercase shadow-sm hover:bg-black hover:text-white dark:border-gray-800 dark:hover:bg-white dark:hover:text-black"
                        >
                            Tipo de Estancia
                        </Button>
                    </div>
                </div>

                <div className="mb-20 grid grid-cols-1 gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {rooms.map((room) => (
                        <TarjetaHabitacion key={room.id} habitacion={room} />
                    ))}
                </div>

                <div className="grid gap-8 border-t border-gray-100 pt-20 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-900">
                    {[
                        {
                            icon: Waves,
                            title: 'Piscina Relax',
                            desc: 'Climatizada y con vistas al jardín',
                        },
                        {
                            icon: UtensilsCrossed,
                            title: 'Gastronomía',
                            desc: 'Platos locales con toque gourmet',
                        },
                        {
                            icon: Car,
                            title: 'Seguridad',
                            desc: 'Estacionamiento vigilado 24/7 de cortesía',
                        },
                        {
                            icon: Wifi,
                            title: 'Wi-Fi de alta velocidad',
                            desc: 'Fibra óptica en cada rincón',
                        },
                    ].map((service, idx) => (
                        <div
                            key={idx}
                            className="group transition-airbnb hover:shadow-airbnb flex flex-col items-center rounded-3xl border border-transparent bg-gray-50 p-8 text-center hover:border-gray-100 hover:bg-white dark:bg-gray-900/50 dark:hover:border-gray-800 dark:hover:bg-gray-900"
                        >
                            <div className="transition-airbnb mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm group-hover:scale-110 group-hover:bg-bugambilia-600 dark:bg-gray-800">
                                <service.icon className="transition-airbnb h-7 w-7 text-bugambilia-600 group-hover:text-white" />
                            </div>
                            <h3 className="mb-2 text-xs font-black tracking-widest text-gray-900 uppercase dark:text-white">
                                {service.title}
                            </h3>
                            <p className="px-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                                {service.desc}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};
export default CuadriculaHabitaciones;
