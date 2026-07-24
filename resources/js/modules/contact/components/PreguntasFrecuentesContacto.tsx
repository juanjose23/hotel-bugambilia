import { usePage } from '@inertiajs/react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/modules/shared/ui/acordeon';
import { Badge } from '@/modules/shared/ui/insignia';
import { Card, CardContent } from '@/modules/shared/ui/tarjeta';
const PreguntasFrecuentesContacto = () => {
    const { hotel } = usePage().props;
    const faqs = [
        {
            question: '¿Cuáles son los horarios de entrada y salida?',
            answer: `La entrada es a partir de las ${hotel.checkin} horas y la salida hasta las ${hotel.checkout} horas. Ofrecemos servicio de entrada temprana y salida tardía sujeto a disponibilidad.`,
        },
        {
            question: '¿Ofrecen transporte desde el aeropuerto?',
            answer: 'Sí, ofrecemos servicio de transporte desde el aeropuerto de Managua. El costo es de $45 USD por trayecto y debe reservarse con al menos 24 horas de anticipación.',
        },
        {
            question: '¿Aceptan mascotas?',
            answer: 'Aceptamos mascotas pequeñas en habitaciones seleccionadas con un cargo adicional de $15 USD por noche. Es necesario notificar al momento de la reserva.',
        },
        {
            question: '¿Qué métodos de pago aceptan?',
            answer: 'Aceptamos efectivo (córdobas y dólares), tarjetas de crédito (Visa, MasterCard, American Express) y transferencias bancarias.',
        },
        {
            question: '¿Tienen Wi-Fi gratuito?',
            answer: 'Sí, ofrecemos Wi-Fi gratuito de alta velocidad en todas las áreas del hotel, incluyendo habitaciones, lobby, restaurante y áreas comunes.',
        },
        {
            question: '¿Cuál es la política de cancelación?',
            answer: 'Las cancelaciones son gratuitas hasta 24 horas antes de la fecha de llegada. Para cancelaciones tardías se cobra el equivalente a una noche.',
        },
        {
            question: '¿Ofrecen tours por la región?',
            answer: 'Sí, organizamos tours por los principales atractivos de Estelí incluyendo fincas de café, reservas naturales y sitios históricos. Consulta en recepción.',
        },
        {
            question: '¿El hotel es accesible para personas con discapacidad?',
            answer: 'Contamos con habitaciones y áreas comunes adaptadas para personas con movilidad reducida, incluyendo rampas y baños accesibles.',
        },
    ];

    return (
        <section className="bg-white py-16 lg:py-24 dark:bg-gray-800">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 text-center">
                    <Badge className="mb-4 border-bugambilia-200 bg-bugambilia-100 text-bugambilia-700 dark:border-bugambilia-700 dark:bg-bugambilia-900/30 dark:text-bugambilia-300">
                        ❓ Preguntas frecuentes
                    </Badge>
                    <h2 className="mb-6 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white">
                        Preguntas frecuentes
                    </h2>
                    <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                        Encuentra respuestas a las preguntas más comunes sobre
                        nuestros servicios y políticas.
                    </p>
                </div>

                <div className="mx-auto max-w-4xl">
                    <Card className="border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <CardContent className="p-6">
                            <Accordion
                                type="single"
                                collapsible
                                className="space-y-4"
                            >
                                {faqs.map((faq, index) => (
                                    <AccordionItem
                                        key={index}
                                        value={`item-${index}`}
                                        className="border-gray-200 dark:border-gray-700"
                                    >
                                        <AccordionTrigger className="text-left text-gray-900 hover:text-bugambilia-600 dark:text-white dark:hover:text-bugambilia-400">
                                            {faq.question}
                                        </AccordionTrigger>
                                        <AccordionContent className="leading-relaxed text-gray-600 dark:text-gray-300">
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
};
export default PreguntasFrecuentesContacto;
