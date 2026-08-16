import { usePage } from '@inertiajs/react';
import { MapPin, Navigation, Car } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

export default function MapaContacto({
    direccion,
}: { direccion?: string } = {}) {
    const pageProps = usePage().props;
    const hotel = pageProps.hotel as
        | { name?: string; direccion?: string; direccion_corta?: string }
        | undefined;
    const hotelName = hotel?.name || 'Hotel Bugambilias';
    const direccionCorta = hotel?.direccion_corta || 'Estelí, Nicaragua';
    const direccionCompleta =
        direccion || hotel?.direccion || 'Salida Sur, Estelí, Nicaragua';

    return (
        <section className="bg-card py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-12 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-4 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <MapPin
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Ubicación
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-4xl lg:text-5xl">
                        Encuéntranos en{' '}
                        <span className="text-bugambilia-600 dark:text-bugambilia-400">
                            Estelí
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Ubicados estratégicamente con fácil acceso a los
                        principales atractivos de la ciudad.
                    </p>
                </div>

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <Card className="overflow-hidden rounded-3xl border-border/80 bg-background">
                            <div className="relative flex h-96 items-center justify-center bg-muted/40">
                                <div className="p-6 text-center">
                                    <MapPin className="mx-auto mb-4 size-16 text-bugambilia-600 dark:text-bugambilia-400" />
                                    <h3 className="mb-2 text-xl font-black text-foreground">
                                        {hotelName}
                                    </h3>
                                    <p className="text-sm font-medium text-muted-foreground">
                                        {direccionCorta}
                                    </p>
                                    <p className="mt-2 text-xs text-muted-foreground/80">
                                        Mapa interactivo disponible próximamente
                                    </p>
                                </div>
                            </div>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card className="rounded-3xl border-border/80 bg-background">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                        <MapPin className="size-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-1 text-sm font-extrabold text-foreground">
                                            Dirección
                                        </h3>
                                        <p className="text-xs text-muted-foreground">
                                            {direccionCompleta}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-border/80 bg-background">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                        <Navigation className="size-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-2 text-sm font-extrabold text-foreground">
                                            Cómo llegar
                                        </h3>
                                        <ul className="flex flex-col gap-1 text-xs text-muted-foreground">
                                            <li>
                                                • 5 min del centro histórico
                                            </li>
                                            <li>
                                                • 10 min de la terminal de buses
                                            </li>
                                            <li>
                                                • 2.5 hrs del aeropuerto de
                                                Managua
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-border/80 bg-background">
                            <CardContent className="p-6">
                                <div className="flex items-start gap-4">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                        <Car className="size-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-2 text-sm font-extrabold text-foreground">
                                            Estacionamiento
                                        </h3>
                                        <p className="text-xs text-muted-foreground">
                                            Parqueo privado gratuito monitoreado
                                            24/7.
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
}
