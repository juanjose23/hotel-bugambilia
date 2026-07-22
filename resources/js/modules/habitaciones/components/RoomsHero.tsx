import { usePage, Link } from '@inertiajs/react';
import { MapPin, Star } from 'lucide-react';
import { Badge } from '@/modules/shared/ui/badge';
import { Button } from '@/modules/shared/ui/button';

export default function RoomsHero() {
    const { hotel } = usePage().props;

    return (
        <section className="relative">
            <div className="relative h-[60vh] min-h-[400px]">
                <img
                    src="/images/pool-front-view.jpg"
                    alt={`Habitaciones ${hotel.name} - Estelí, Nicaragua`}
                    className="absolute inset-0 h-full w-full object-cover"
                />

                <div className="absolute inset-0 bg-gradient-to-r from-bugambilia-900/70 via-bugambilia-800/50 to-transparent dark:from-black/80 dark:via-bugambilia-900/60 dark:to-bugambilia-800/40" />

                <div className="bugambilia-pattern absolute inset-0 opacity-20" />

                <div className="absolute inset-0 flex items-center">
                    <div className="container mx-auto px-4">
                        <div className="max-w-2xl text-white">
                            <div className="mb-4 flex items-center gap-2">
                                <Badge className="border-0 bg-primary text-primary-foreground">
                                    Habitaciones disponibles
                                </Badge>
                                <div className="flex items-center gap-1">
                                    {[...Array(5)].map((_, i) => (
                                        <Star
                                            key={i}
                                            className="h-4 w-4 fill-yellow-400 text-yellow-400"
                                        />
                                    ))}
                                </div>
                            </div>

                            <h1 className="bugambilia-bloom mb-4 text-4xl font-bold md:text-6xl">
                                Nuestras Habitaciones
                                <span className="mt-2 block text-xl text-white/90 md:text-2xl">
                                    Comodidad y elegancia en Estelí
                                </span>
                            </h1>

                            <p className="mb-6 text-lg text-white/80 md:text-xl">
                                Descubre nuestras habitaciones diseñadas para
                                brindarte el máximo confort durante tu estancia
                                en Nicaragua
                            </p>

                            <div className="mb-8 flex items-center gap-2 text-white/90">
                                <MapPin className="h-5 w-5" />
                                <span>
                                    Estelí, Nicaragua - En el corazón de la
                                    ciudad
                                </span>
                            </div>

                            <div className="flex flex-col gap-4 sm:flex-row">
                                <Button
                                    size="lg"
                                    className="petal-shadow px-8 py-3 transition-all duration-300 hover:scale-105"
                                    asChild
                                >
                                    <Link href="#habitaciones">
                                        Ver habitaciones
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="lg"
                                    className="border-white bg-transparent px-8 py-3 text-white backdrop-blur-sm hover:bg-white hover:text-primary dark:hover:bg-primary/20 dark:hover:text-white"
                                    asChild
                                >
                                    <Link href="/contacto">
                                        Consultar disponibilidad
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
