import { usePage } from '@inertiajs/react';
import { MapPin, Navigation, Car } from 'lucide-react';
import { Badge } from '@/modules/shared/ui/insignia';
import { Card, CardContent } from '@/modules/shared/ui/tarjeta';
const MapaContacto = () => {
    const { hotel } = usePage().props;

    return (
        <section className="bg-gray-50 py-16 lg:py-24 dark:bg-gray-900">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 text-center">
                    <Badge className="mb-4 border-bugambilia-200 bg-bugambilia-100 text-bugambilia-700 dark:border-bugambilia-700 dark:bg-bugambilia-900/30 dark:text-bugambilia-300">
                        Ubicación
                    </Badge>
                    <h2 className="mb-6 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white">
                        Encuéntranos en Estelí
                    </h2>
                    <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                        Ubicados estratégicamente en el centro de Estelí, con
                        fácil acceso a los principales atractivos de la ciudad.
                    </p>
                </div>

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <Card className="overflow-hidden border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <div className="relative flex h-96 items-center justify-center bg-gray-100 dark:bg-gray-800">
                                <div className="text-center">
                                    <MapPin className="mx-auto mb-4 h-16 w-16 text-bugambilia-600 dark:text-bugambilia-400" />
                                    <h3 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                        {hotel.name}
                                    </h3>
                                    <p className="text-gray-600 dark:text-gray-300">
                                        {hotel.direccion_corta}
                                    </p>
                                    <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Mapa interactivo disponible próximamente
                                    </p>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-bugambilia-100 dark:bg-bugambilia-900/30">
                                        <MapPin className="h-6 w-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                            Dirección
                                        </h3>
                                        <p className="mb-1 text-gray-600 dark:text-gray-300">
                                            {hotel.direccion
                                                .split(',')[0]
                                                .trim()}
                                        </p>
                                        <p className="text-gray-600 dark:text-gray-300">
                                            {hotel.direccion_corta}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-bugambilia-100 dark:bg-bugambilia-900/30">
                                        <Navigation className="h-6 w-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                            Cómo llegar
                                        </h3>
                                        <ul className="space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                            <li>
                                                • 5 min del centro histórico
                                            </li>
                                            <li>
                                                • 10 min de la terminal de buses
                                            </li>
                                            <li>
                                                • 45 min del aeropuerto de
                                                Managua
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-bugambilia-100 dark:bg-bugambilia-900/30">
                                        <Car className="h-6 w-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                            Estacionamiento
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-300">
                                            Estacionamiento gratuito disponible
                                            para todos nuestros huéspedes.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </section>
    );
};
export default MapaContacto;
