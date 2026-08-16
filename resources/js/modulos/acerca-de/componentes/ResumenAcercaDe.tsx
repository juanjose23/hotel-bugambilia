import { usePage, Link } from '@inertiajs/react';
import { Heart, Users, Award, Leaf, ArrowRight, Star } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';

const destacadosDefecto = [
    {
        icon: Heart,
        title: 'Hospitalidad Nicaragüense',
        description: 'Atención cálida y personalizada en cada momento.',
    },
    {
        icon: Users,
        title: 'Tradición Familiar',
        description: 'Más de 35 años recibiendo huéspedes de todo el mundo.',
    },
    {
        icon: Award,
        title: 'Calidad 5 Estrellas',
        description: 'Certificados de excelencia e instalaciones boutique.',
    },
    {
        icon: Leaf,
        title: 'Compromiso Sostenible',
        description: 'Prácticas eco-amigables y respeto por la naturaleza.',
    },
];

export default function ResumenAcercaDe() {
    const pageProps = usePage().props;
    const hotelName =
        (pageProps.hotel as { name?: string })?.name || 'Hotel Bugambilias';
    const fundado = String(
        (pageProps.hotel as { fundado?: number | string })?.fundado ?? '1989',
    );

    return (
        <section className="overflow-hidden border-b border-border/40 bg-background py-24 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
                    {/* Left Text Block */}
                    <div className="flex flex-col gap-6 lg:col-span-6">
                        <div>
                            <Badge
                                variant="outline"
                                className="border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                            >
                                <Star
                                    className="mr-1 size-3.5 fill-bugambilia-500"
                                    data-icon="inline-start"
                                />{' '}
                                Nuestra Historia & Legado
                            </Badge>
                        </div>

                        <h2 className="text-3xl font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Más de 35 años{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                floreciendo en Estelí
                            </span>
                        </h2>

                        <p className="text-base leading-relaxed text-muted-foreground sm:text-lg">
                            Desde {fundado}, {hotelName} ha sido el santuario
                            preferido por huéspedes que buscan confort boutique,
                            tranquilidad y la auténtica calidez de Nicaragua.
                        </p>

                        {/* Highlights Grid */}
                        <div className="grid gap-6 pt-2 sm:grid-cols-2">
                            {destacadosDefecto.map((item, index) => (
                                <div
                                    key={index}
                                    className="flex items-start gap-3.5"
                                >
                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                        <item.icon className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                    </div>
                                    <div>
                                        <h3 className="mb-1 text-sm font-extrabold text-foreground">
                                            {item.title}
                                        </h3>
                                        <p className="text-xs leading-relaxed text-muted-foreground">
                                            {item.description}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="pt-4">
                            <Button
                                asChild
                                size="lg"
                                className="rounded-full bg-foreground font-extrabold text-background hover:bg-foreground/90"
                            >
                                <Link href="/acerca-de" prefetch>
                                    Conozca más sobre nosotros
                                    <ArrowRight
                                        className="size-4"
                                        data-icon="inline-end"
                                    />
                                </Link>
                            </Button>
                        </div>
                    </div>

                    {/* Right Media Banner */}
                    <div className="relative lg:col-span-6">
                        <div className="relative aspect-4/3 overflow-hidden rounded-3xl border border-border/80 shadow-2xl">
                            <img
                                src="/images/hero-secondary.webp"
                                alt={`${hotelName} - Historia y tradición`}
                                className="h-full w-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
