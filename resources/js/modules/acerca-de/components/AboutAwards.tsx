import { Award, Star, Heart, Leaf } from 'lucide-react';
import { Badge } from '@/modules/shared/ui/badge';
import { Card, CardContent } from '@/modules/shared/ui/card';

const awards = [
    {
        icon: Award,
        title: 'Mejor Hotel 2023',
        organization: 'Cámara de Turismo de Nicaragua',
        year: '2023',
        description:
            'Reconocimiento por excelencia en servicio y hospitalidad.',
    },
    {
        icon: Star,
        title: 'Certificación de Calidad',
        organization: 'Instituto Nicaragüense de Turismo',
        year: '2022',
        description: 'Certificación por mantener altos estándares de calidad.',
    },
    {
        icon: Heart,
        title: 'Premio a la Hospitalidad',
        organization: 'Asociación Hotelera de Estelí',
        year: '2021',
        description:
            'Por el trato excepcional a huéspedes nacionales e internacionales.',
    },
    {
        icon: Leaf,
        title: 'Hotel Sostenible',
        organization: 'Green Hotels Nicaragua',
        year: '2020',
        description:
            'Reconocimiento por prácticas ambientalmente responsables.',
    },
];

export default function AboutAwards() {
    return (
        <section className="bg-bugambilia-50 py-16 lg:py-24 dark:bg-gray-900">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-16 text-center">
                    <Badge className="mb-4 border-0 bg-bugambilia-600 text-white">
                        Reconocimientos
                    </Badge>
                    <h2 className="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl dark:text-white">
                        Premios y
                        <span className="block text-bugambilia-600 dark:text-bugambilia-400">
                            Certificaciones
                        </span>
                    </h2>
                    <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                        Nuestro compromiso con la excelencia ha sido reconocido
                        por diversas organizaciones del sector turístico.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {awards.map((award, index) => (
                        <Card
                            key={index}
                            className="border-gray-200 bg-white transition-shadow duration-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <CardContent className="p-6 text-center">
                                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-bugambilia-100 dark:bg-bugambilia-900/30">
                                    <award.icon className="h-8 w-8 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <div className="mb-2 text-sm font-medium text-bugambilia-600 dark:text-bugambilia-400">
                                    {award.year}
                                </div>
                                <h3 className="mb-2 text-lg font-bold text-gray-900 dark:text-white">
                                    {award.title}
                                </h3>
                                <p className="mb-3 text-sm text-gray-500 dark:text-gray-400">
                                    {award.organization}
                                </p>
                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                    {award.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
