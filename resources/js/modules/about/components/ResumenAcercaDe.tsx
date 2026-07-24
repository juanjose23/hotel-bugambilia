import { usePage, Link } from '@inertiajs/react';
import { Heart, Users, Award, Leaf, ArrowRight, Star } from 'lucide-react';
const highlights = [
    {
        icon: Heart,
        title: 'Hospitalidad Nicaragüense',
        description:
            'Atención cálida, respetuosa y altamente personalizada en cada momento.',
    },
    {
        icon: Users,
        title: 'Tradición Familiar',
        description:
            'Más de 35 años recibiendo familias y ejecutivos de todo el mundo.',
    },
    {
        icon: Award,
        title: 'Calidad 5 Estrellas',
        description:
            'Certificados de excelencia e instalaciones de nivel internacional.',
    },
    {
        icon: Leaf,
        title: 'Compromiso Sostenible',
        description:
            'Prácticas eco-amigables y valor de la naturaleza nicaragüense.',
    },
];
const ResumenAcercaDe = () => {
    const pageProps = usePage().props;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';
    const fundado = pageProps.hotel?.fundado || '1989';

    return (
        <section className="overflow-hidden border-b border-border/40 bg-background py-24 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
                    {/* Left Text Block */}
                    <div className="lg:col-span-6">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                            <Star className="h-3.5 w-3.5 fill-bugambilia-500" />
                            Nuestra Historia & Legado
                        </div>

                        <h2 className="mb-4 text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Más de 35 años{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                floreciendo en Estelí
                            </span>
                        </h2>

                        <p className="mb-8 text-base leading-relaxed text-muted-foreground sm:text-lg">
                            Desde {fundado}, {hotelName} ha sido el santuario
                            preferido por huéspedes que buscan confort boutique,
                            tranquilidad y la auténtica calidez de Nicaragua.
                            Cada rincón ha sido diseñado para ofrecer descanso
                            de primer nivel.
                        </p>

                        {/* Highlights Grid */}
                        <div className="mb-10 grid gap-6 sm:grid-cols-2">
                            {highlights.map((item, index) => (
                                <div
                                    key={index}
                                    className="flex items-start gap-3.5"
                                >
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                        <item.icon className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
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

                        <Link
                            href="/acerca-de"
                            className="shadow-airbnb inline-flex items-center gap-2.5 rounded-full bg-foreground px-7 py-3.5 text-xs font-extrabold tracking-wider text-background uppercase transition-all duration-300 hover:scale-105 hover:bg-foreground/90"
                        >
                            <span>Conozca más sobre nosotros</span>
                            <ArrowRight className="h-4 w-4" />
                        </Link>
                    </div>

                    {/* Right Media Banner */}
                    <div className="relative lg:col-span-6">
                        <div className="shadow-airbnb-hover relative aspect-[4/3] overflow-hidden rounded-3xl border border-border/80">
                            <img
                                src="/images/hero-secondary.webp"
                                alt={`${hotelName} - Historia y tradición`}
                                className="h-full w-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                        </div>

                        {/* Experience Floating Pill Badge */}
                        <div className="absolute -bottom-6 -left-4 flex animate-float items-center gap-4 rounded-2xl border border-border/80 bg-card p-5 shadow-2xl sm:left-6">
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-bugambilia-600 text-2xl font-black text-white shadow-lg">
                                35+
                            </div>
                            <div>
                                <p className="text-sm font-black text-foreground">
                                    Años de Excelencia
                                </p>
                                <p className="text-xs font-medium text-muted-foreground">
                                    Hospitalidad en Estelí, Nicaragua
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};
export default ResumenAcercaDe;
