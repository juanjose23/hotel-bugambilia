import {
    Wifi,
    Car,
    Coffee,
    Tv,
    Wind,
    Shield,
    Clock,
    UtensilsCrossed,
} from 'lucide-react';
import { Card, CardContent } from '@/modules/shared/ui/card';

const amenities = [
    {
        icon: Wifi,
        name: 'Wi-Fi Gratuito',
        description: 'Internet de alta velocidad en todas las áreas',
    },
    {
        icon: Car,
        name: 'Estacionamiento',
        description: 'Espacio seguro y gratuito para tu vehículo',
    },
    {
        icon: Coffee,
        name: 'Cafetera',
        description: 'Café nicaragüense fresco en tu habitación',
    },
    {
        icon: Tv,
        name: 'TV Cable',
        description: 'Canales nacionales e internacionales',
    },
    {
        icon: Wind,
        name: 'Aire Acondicionado',
        description: 'Clima perfecto durante todo el año',
    },
    {
        icon: Shield,
        name: 'Caja Fuerte',
        description: 'Protege tus objetos de valor',
    },
    {
        icon: Clock,
        name: 'Servicio 24h',
        description: 'Recepción disponible las 24 horas',
    },
    {
        icon: UtensilsCrossed,
        name: 'Servicio a la Habitación',
        description: 'Comida y bebidas cuando lo necesites',
    },
];

export default function RoomAmenities() {
    return (
        <section className="bg-white py-16 dark:bg-gray-900">
            <div className="container mx-auto px-4">
                <div className="mb-12 text-center">
                    <h2 className="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white">
                        Amenidades Incluidas
                        <span className="mt-2 block text-lg text-bugambilia-600 dark:text-bugambilia-400">
                            Todo lo que necesitas para una estancia perfecta
                        </span>
                    </h2>
                    <p className="mx-auto max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                        Cada habitación incluye una selección cuidadosa de
                        amenidades para garantizar tu comodidad y satisfacción.
                    </p>
                </div>

                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {amenities.map((amenity, index) => (
                        <Card
                            key={index}
                            className="petal-shadow bugambilia-bloom border-gray-200 bg-white text-center transition-all duration-300 hover:scale-105 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <CardContent className="p-6">
                                <div className="petal-shadow mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-bugambilia-100 dark:bg-bugambilia-900/30">
                                    <amenity.icon className="h-6 w-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                    {amenity.name}
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                    {amenity.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
