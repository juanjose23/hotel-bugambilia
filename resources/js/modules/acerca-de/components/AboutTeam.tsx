import { usePage } from '@inertiajs/react';
import { Badge } from '@/modules/shared/ui/badge';
import { Card, CardContent } from '@/modules/shared/ui/card';

const team = [
    {
        name: 'María Elena Martínez',
        role: 'Directora General',
        description:
            'Fundadora y alma del hotel, con más de 35 años dedicados a la hospitalidad nicaragüense.',
        image: '/placeholder.svg?height=300&width=300&text=María+Elena',
    },
    {
        name: 'Carlos Martínez',
        role: 'Gerente de Operaciones',
        description:
            'Especialista en turismo sostenible, garantiza la excelencia en cada servicio del hotel.',
        image: '/placeholder.svg?height=300&width=300&text=Carlos',
    },
    {
        name: 'Ana Lucía Herrera',
        role: 'Chef Ejecutiva',
        description:
            'Maestra de la cocina nicaragüense, combina tradición y modernidad en cada platillo.',
        image: '/placeholder.svg?height=300&width=300&text=Ana+Lucía',
    },
    {
        name: 'Roberto Sánchez',
        role: 'Concierge Principal',
        description:
            'Experto en la región de Estelí, ayuda a los huéspedes a descubrir los tesoros locales.',
        image: '/placeholder.svg?height=300&width=300&text=Roberto',
    },
];

export default function AboutTeam() {
    const { hotel } = usePage().props;

    return (
        <section className="bg-gray-50 py-16 lg:py-24 dark:bg-gray-900">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-16 text-center">
                    <Badge className="mb-4 border-bugambilia-200 bg-bugambilia-100 text-bugambilia-700 dark:border-bugambilia-700 dark:bg-bugambilia-900/30 dark:text-bugambilia-300">
                        Nuestro Equipo
                    </Badge>
                    <h2 className="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl dark:text-white">
                        Las personas detrás de
                        <span className="block text-bugambilia-600 dark:text-bugambilia-400">
                            la experiencia {hotel.name.replace('Hotel ', '')}
                        </span>
                    </h2>
                    <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                        Conoce al equipo apasionado que hace posible que cada
                        estancia en nuestro hotel sea memorable.
                    </p>
                </div>

                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {team.map((member, index) => (
                        <Card
                            key={index}
                            className="overflow-hidden border-gray-200 bg-white transition-shadow duration-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div className="relative h-64">
                                <img
                                    src={member.image || '/placeholder.svg'}
                                    alt={member.name}
                                    className="absolute inset-0 h-full w-full object-cover"
                                />
                            </div>
                            <CardContent className="p-6">
                                <h3 className="mb-2 text-xl font-bold text-gray-900 dark:text-white">
                                    {member.name}
                                </h3>
                                <p className="mb-3 font-medium text-bugambilia-600 dark:text-bugambilia-400">
                                    {member.role}
                                </p>
                                <p className="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                    {member.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
