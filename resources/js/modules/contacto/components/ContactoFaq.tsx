import { HelpCircle } from 'lucide-react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/modules/shared/components/ui/accordion';
import type { PreguntaFrecuenteItem } from '../types';

const FAQS: PreguntaFrecuenteItem[] = [
    {
        id: '1',
        pregunta: '¿Cuáles son los horarios de Check-in y Check-out?',
        respuesta:
            'El horario regular de Check-in inicia a las 2:00 PM y el Check-out es hasta las 12:00 PM del mediodía. Si necesitas ingreso temprano (Early Check-in) o salida tardía (Late Check-out), contáctanos con anticipación para coordinar disponibilidad.',
    },
    {
        id: '2',
        pregunta: '¿El estacionamiento privado tiene costo adicional?',
        respuesta:
            'No, el estacionamiento cerrado y seguro es completamente gratuito para todos nuestros huéspedes durante su estancia en el hotel.',
    },
    {
        id: '3',
        pregunta: '¿Qué formas de pago aceptan en recepción?',
        respuesta:
            'Aceptamos pagos en efectivo (Córdobas y Dólares USD), tarjetas de débito y crédito (Visa, MasterCard, American Express), así como transferencias bancarias locales e internacionales (BAC, Banpro, Lafise).',
    },
    {
        id: '4',
        pregunta: '¿Ofrecen tarifas especiales para empresas o grupos?',
        respuesta:
            'Sí, disponemos de convenios corporativos y paquetes con descuentos especiales para empresas, delegaciones, bodas y eventos sociales de grupos.',
    },
    {
        id: '5',
        pregunta: '¿Tienen restaurante y servicio de desayuno?',
        respuesta:
            'Contamos con Restaurante Absoluto dentro de las instalaciones, ofreciendo desayunos típicos e internacionales, almuerzos, cenas y coctelería artesanal.',
    },
];

export const ContactoFaq = () => {
    return (
        <section
            aria-labelledby="titulo-faq"
            className="bg-background py-10 md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-2xl text-center">
                    <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-widest text-primary uppercase dark:text-rose-400">
                        <HelpCircle className="size-3.5" />
                        <span>Preguntas Frecuentes</span>
                    </div>
                    <h2
                        id="titulo-faq"
                        className="mt-1 text-xl font-black tracking-tight text-foreground sm:text-3xl"
                    >
                        Respuestas Rápidas para tu Viaje
                    </h2>
                    <p className="mt-2 text-xs text-muted-foreground sm:text-sm">
                        Todo lo que necesitas saber antes de tu llegada a Hotel
                        Bugambilias.
                    </p>
                </div>

                <div className="mx-auto mt-8 max-w-3xl rounded-3xl border border-border bg-card p-6 shadow-xs sm:p-8">
                    <Accordion className="w-full">
                        {FAQS.map((faq) => (
                            <AccordionItem key={faq.id} value={faq.id}>
                                <AccordionTrigger className="text-left text-xs font-bold text-foreground sm:text-sm">
                                    {faq.pregunta}
                                </AccordionTrigger>
                                <AccordionContent className="text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                    {faq.respuesta}
                                </AccordionContent>
                            </AccordionItem>
                        ))}
                    </Accordion>
                </div>
            </div>
        </section>
    );
};

export default ContactoFaq;
