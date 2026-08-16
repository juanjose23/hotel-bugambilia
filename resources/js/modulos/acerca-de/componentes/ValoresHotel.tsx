import { usePage } from '@inertiajs/react';
import {
    Heart,
    Shield,
    Leaf,
    Users,
    Star,
    Globe,
    BadgeCheck,
} from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { TarjetaValorItem } from './secciones/TarjetaValorItem';

const valoresDefecto = [
    {
        icono: Heart,
        titulo: 'Hospitalidad Auténtica',
        descripcion:
            'Servicio cálido y atento que refleja la amabilidad nicaragüense.',
    },
    {
        icono: Shield,
        titulo: 'Calidad & Confianza',
        descripcion:
            'Mantenemos los más altos estándares de higiene, seguridad y confort.',
    },
    {
        icono: Leaf,
        titulo: 'Sostenibilidad',
        descripcion:
            'Comprometidos con el desarrollo ambientalmente responsable en Estelí.',
    },
    {
        icono: Users,
        titulo: 'Comunidad Local',
        descripcion:
            'Apoyamos a productores, artesanos y colaboradores locales.',
    },
    {
        icono: Star,
        titulo: 'Excelencia de Servicio',
        descripcion:
            'Cuidado por los detalles para superar las expectativas de cada estancia.',
    },
    {
        icono: Globe,
        titulo: 'Identidad Nicaragüense',
        descripcion:
            'Promovemos la cultura, gastronomía y paisajes del norte de Nicaragua.',
    },
];

export default function ValoresHotel() {
    const pageProps = usePage().props;
    const hotelName =
        (pageProps.hotel as { name?: string })?.name || 'Hotel Bugambilias';

    return (
        <section className="border-b border-border/40 bg-card py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <BadgeCheck
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Nuestros Pilares
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Valores que Guían nuestra{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Excelencia
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Principios fundamentales que definen cada experiencia en{' '}
                        {hotelName}.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {valoresDefecto.map((valor, index) => (
                        <TarjetaValorItem
                            key={index}
                            titulo={valor.titulo}
                            descripcion={valor.descripcion}
                            Icono={valor.icono}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}
