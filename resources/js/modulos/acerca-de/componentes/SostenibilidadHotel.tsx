import { usePage } from '@inertiajs/react';
import { Leaf, Droplets, Recycle, Users } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

const iniciativasDefecto = [
    {
        icono: Leaf,
        titulo: 'Energía Renovable',
        descripcion:
            'Paneles solares para el 60% de consumo energético, reduciendo la huella de carbono.',
    },
    {
        icono: Droplets,
        titulo: 'Conservación del Agua',
        descripcion:
            'Sistema de recolección de agua lluvia y reciclamiento para riego de jardines.',
    },
    {
        icono: Recycle,
        titulo: 'Gestión de Residuos',
        descripcion:
            'Programa de reciclaje y compostaje reduciendo un 80% los residuos.',
    },
    {
        icono: Users,
        titulo: 'Comunidad Local',
        descripcion:
            'Empleamos personal local y compramos a productores de Estelí.',
    },
];

export default function SostenibilidadHotel() {
    const pageProps = usePage().props;
    const hotelName =
        (pageProps.hotel as { name?: string })?.name || 'Hotel Bugambilias';

    return (
        <section className="bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-4 border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                    >
                        <Leaf
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Sostenibilidad
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Compromiso con el{' '}
                        <span className="text-emerald-600 dark:text-emerald-400">
                            Medio Ambiente
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        En {hotelName} creemos que el turismo responsable es
                        clave para preservar la belleza natural de Nicaragua.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    {iniciativasDefecto.map((iniciativa, index) => (
                        <Card
                            key={index}
                            className="rounded-3xl border-border/80 bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                        >
                            <CardContent className="flex flex-col items-center p-6 text-center">
                                <div className="mb-4 flex size-14 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                                    <iniciativa.icono className="size-7 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h3 className="mb-2 text-lg font-extrabold text-foreground">
                                    {iniciativa.titulo}
                                </h3>
                                <p className="text-xs leading-relaxed text-muted-foreground">
                                    {iniciativa.descripcion}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
