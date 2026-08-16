import { CheckCircle2, Star } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesComodidadesHabitacion {
    comodidades?: string[];
}

export const ComodidadesHabitacion = ({
    comodidades = [],
}: PropiedadesComodidadesHabitacion) => {
    if (!comodidades || comodidades.length === 0) {
        return null;
    }

    return (
        <section className="border-t border-border/40 bg-background py-16 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <Star
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Equipamiento de la Estancia
                    </Badge>
                    <h2 className="text-3xl font-black text-foreground md:text-4xl">
                        Amenidades Incluidas
                    </h2>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {comodidades.map((item, index) => (
                        <Card
                            key={index}
                            className="rounded-2xl border-border/60 bg-card p-4 transition-all duration-200 hover:border-border hover:shadow-md"
                        >
                            <CardContent className="flex items-center gap-3 p-0">
                                <CheckCircle2 className="size-5 shrink-0 text-emerald-500" />
                                <span className="text-xs font-bold text-foreground">
                                    {item}
                                </span>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default ComodidadesHabitacion;
