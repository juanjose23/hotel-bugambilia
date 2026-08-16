import { MessageSquareQuote } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { TestimonioInicio } from '../interfaces/inicio';
import { TarjetaTestimonioItem } from './secciones/TarjetaTestimonioItem';

interface PropiedadesSeccionTestimonios {
    testimonios?: TestimonioInicio[];
}

export const SeccionTestimonios = ({
    testimonios = [],
}: PropiedadesSeccionTestimonios) => {
    if (!testimonios || testimonios.length === 0) {
        return null;
    }

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <MessageSquareQuote
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Opiniones de Huéspedes
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Experiencias{' '}
                        <span className="text-bugambilia-600 dark:text-bugambilia-400">
                            Inolvidables
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Descubra lo que nuestros clientes opinan sobre su
                        estancia en Hotel Bugambilias.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {testimonios.map((testimonio) => (
                        <TarjetaTestimonioItem
                            key={testimonio.id}
                            testimonio={testimonio}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
};

export default SeccionTestimonios;
