import { usePage } from '@inertiajs/react';
import {
    Heart,
    Shield,
    Leaf,
    Users,
    Star,
    Globe,
    Sparkles,
} from 'lucide-react';
const values = [
    {
        icon: Heart,
        title: 'Hospitalidad Auténtica',
        description:
            'Servicio cálido y atento que refleja la calidez humana y la amabilidad nicaragüense.',
    },
    {
        icon: Shield,
        title: 'Calidad & Confianza',
        description:
            'Mantenemos los más altos estándares de higiene, seguridad y confort en cada espacio.',
    },
    {
        icon: Leaf,
        title: 'Sostenibilidad',
        description:
            'Comprometidos con el desarrollo ambientalmente responsable en la región de Estelí.',
    },
    {
        icon: Users,
        title: 'Comunidad Local',
        description:
            'Apoyamos a productores, artesanos y colaboradores locales generando oportunidades.',
    },
    {
        icon: Star,
        title: 'Excelencia de Servicio',
        description:
            'Cuidado obsesivo por los detalles para superar las expectativas de cada estancia.',
    },
    {
        icon: Globe,
        title: 'Identidad Nicaragüense',
        description:
            'Promovemos el orgullo por la cultura, gastronomía y paisajes del norte de Nicaragua.',
    },
];
const ValoresHotel = () => {
    const pageProps = usePage().props;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';

    return (
        <section className="border-b border-border/40 bg-card py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                        <Sparkles className="h-3.5 w-3.5" />
                        Nuestros Pilares
                    </div>
                    <h2 className="mb-4 text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Valores que Guían nuestra{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Excelencia
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Principios fundamentales que definen cada experiencia y
                        servicio que brindamos en {hotelName}.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {values.map((value, index) => (
                        <div
                            key={index}
                            className="shadow-airbnb hover:shadow-airbnb-hover flex flex-col items-center rounded-3xl border border-border/80 bg-background p-8 text-center transition-all duration-300 hover:-translate-y-1"
                        >
                            <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                <value.icon className="h-7 w-7 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <h3 className="mb-2 text-lg font-extrabold text-foreground">
                                {value.title}
                            </h3>
                            <p className="text-xs leading-relaxed text-muted-foreground">
                                {value.description}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};
export default ValoresHotel;
