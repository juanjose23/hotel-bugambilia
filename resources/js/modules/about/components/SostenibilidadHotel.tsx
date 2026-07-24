import { usePage } from '@inertiajs/react';
import { Leaf, Droplets, Recycle, Users } from 'lucide-react';
import { Badge } from '@/modules/shared/ui/insignia';
import { Card, CardContent } from '@/modules/shared/ui/tarjeta';
const initiatives = [
    {
        icon: Leaf,
        title: 'Energía Renovable',
        description:
            'Utilizamos paneles solares para el 60% de nuestro consumo energético, reduciendo nuestra huella de carbono.',
    },
    {
        icon: Droplets,
        title: 'Conservación del Agua',
        description:
            'Sistema de recolección de agua lluvia y tratamiento de aguas grises para riego de jardines.',
    },
    {
        icon: Recycle,
        title: 'Gestión de Residuos',
        description:
            'Programa integral de reciclaje y compostaje, reduciendo un 80% los residuos enviados a vertederos.',
    },
    {
        icon: Users,
        title: 'Comunidad Local',
        description:
            'Empleamos personal local y compramos productos de agricultores y artesanos de la región de Estelí.',
    },
];
const SostenibilidadHotel = () => {
    const { hotel } = usePage().props;

    return (
        <section className="bg-white py-16 lg:py-24 dark:bg-gray-800">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-16 text-center">
                    <Badge className="mb-4 border-green-200 bg-green-100 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300">
                        Sostenibilidad
                    </Badge>
                    <h2 className="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl dark:text-white">
                        Compromiso con el
                        <span className="block text-green-600 dark:text-green-400">
                            medio ambiente
                        </span>
                    </h2>
                    <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                        En {hotel.name} creemos que el turismo responsable es
                        clave para preservar la belleza natural de Nicaragua
                        para las futuras generaciones.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {initiatives.map((initiative, index) => (
                        <Card
                            key={index}
                            className="border-gray-200 bg-white transition-shadow duration-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
                        >
                            <CardContent className="p-6 text-center">
                                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                    <initiative.icon className="h-8 w-8 text-green-600 dark:text-green-400" />
                                </div>
                                <h3 className="mb-3 text-lg font-bold text-gray-900 dark:text-white">
                                    {initiative.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                    {initiative.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
};
export default SostenibilidadHotel;
