import { usePage } from '@inertiajs/react';
import { HelpCircle } from 'lucide-react';
import {
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent,
} from '@/modulos/compartido/ui/acordeon';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { PREGUNTAS_FRECUENTES_DEFECTO } from '../constantes/contactoConstantes';

export default function PreguntasFrecuentesContacto() {
    const pageProps = usePage().props;
    const hotel = pageProps.hotel as
        { checkin?: string; checkout?: string } | undefined;
    const checkin = hotel?.checkin || '14:00';
    const checkout = hotel?.checkout || '12:00';

    const preguntas = PREGUNTAS_FRECUENTES_DEFECTO.map((item, index) => {
        if (index === 0) {
            return {
                ...item,
                answer: `La entrada es a partir de las ${checkin} horas y la salida hasta las ${checkout} horas. Ofrecemos servicio de entrada temprana y salida tardía sujeto a disponibilidad.`,
            };
        }

        return item;
    });

    return (
        <section className="bg-background py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-12 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-4 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <HelpCircle
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Preguntas Frecuentes
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-4xl lg:text-5xl">
                        Preguntas{' '}
                        <span className="text-bugambilia-600 dark:text-bugambilia-400">
                            Frecuentes
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Encuentra respuestas a las consultas más comunes sobre
                        nuestros servicios y políticas.
                    </p>
                </div>

                <div className="mx-auto max-w-4xl">
                    <Card className="rounded-3xl border-border/80 bg-card">
                        <CardContent className="p-6">
                            <Accordion
                                type="single"
                                collapsible
                                className="flex flex-col gap-4"
                            >
                                {preguntas.map((faq, index) => (
                                    <AccordionItem
                                        key={index}
                                        value={`item-${index}`}
                                        className="border-border/60"
                                    >
                                        <AccordionTrigger className="text-left font-bold text-foreground hover:text-bugambilia-600 dark:hover:text-bugambilia-400">
                                            {faq.question}
                                        </AccordionTrigger>
                                        <AccordionContent className="leading-relaxed text-muted-foreground">
                                            {faq.answer}
                                        </AccordionContent>
                                    </AccordionItem>
                                ))}
                            </Accordion>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    );
}
