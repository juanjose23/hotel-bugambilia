import {
    ShieldCheck,
    Coffee,
    CalendarCheck,
    CreditCard,
    Car,
    Clock,
    MessageCircle,
} from 'lucide-react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/modules/shared/components/ui/accordion';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const BookingCtaBanner = () => {
    const { hotel } = usePropiedadesPagina();
    const telefonoWhatsApp = (hotel?.whatsapp || '+50587136805').replace(
        /\D/g,
        '',
    );
    const whatsappUrl = `https://wa.me/${telefonoWhatsApp}?text=${encodeURIComponent('Hola Hotel Bugambilias, deseo consultar disponibilidad para una reserva.')}`;

    return (
        <section
            aria-labelledby="titulo-cta-banner"
            className="border-t border-border bg-card/50 py-12 font-sans md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                {/* Banner de Reserva Directa */}
                <div className="relative overflow-hidden rounded-3xl border border-primary/20 bg-primary/95 p-8 text-primary-foreground shadow-2xl sm:p-12 lg:p-14 dark:border-rose-500/30 dark:bg-rose-950/90">
                    <div className="relative z-10 grid grid-cols-1 items-center gap-8 lg:grid-cols-12">
                        <div className="lg:col-span-8">
                            <h2
                                id="titulo-cta-banner"
                                className="text-2xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl"
                            >
                                Tu mejor tarifa garantizada en Hotel Bugambilias
                            </h2>

                            <p className="mt-3 max-w-xl text-xs leading-relaxed text-white/90 sm:text-sm">
                                Reserva directamente con nuestro equipo de
                                recepción vía WhatsApp o en línea y obtén trato
                                preferencial, check-in ágil y la experiencia más
                                confortable en Estelí.
                            </p>

                            {/* Puntos de Confianza */}
                            <div className="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div className="flex items-center gap-2 text-xs font-bold text-white">
                                    <ShieldCheck className="size-4 text-emerald-300" />
                                    <span>Mejor Precio Garantizado</span>
                                </div>
                                <div className="flex items-center gap-2 text-xs font-bold text-white">
                                    <Coffee className="size-4 text-amber-300" />
                                    <span>Café de Altura Incluido</span>
                                </div>
                                <div className="flex items-center gap-2 text-xs font-bold text-white">
                                    <CalendarCheck className="size-4 text-rose-200" />
                                    <span>Atención Directa 24/7</span>
                                </div>
                            </div>
                        </div>

                        {/* Botón WhatsApp */}
                        <div className="flex flex-col gap-2.5 lg:col-span-4 lg:items-end">
                            <a
                                href={whatsappUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full bg-emerald-600 px-6 py-3.5 text-xs font-black text-white shadow-xl transition-all duration-300 hover:scale-105 hover:bg-emerald-700 active:scale-95 sm:w-auto sm:text-sm"
                            >
                                <MessageCircle className="size-4" />
                                <span>Reservar por WhatsApp</span>
                            </a>
                            <p className="text-center text-[11px] font-medium text-white/70 sm:text-right">
                                Recepción en Estelí • +505 8713 6805
                            </p>
                        </div>
                    </div>
                </div>

                {/* Preguntas Frecuentes */}
                <div className="mx-auto mt-14 max-w-3xl">
                    <div className="mb-6 text-center">
                        <span className="text-[11px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                            Resuelve tus dudas
                        </span>
                        <h3 className="mt-1 text-xl font-black text-foreground sm:text-2xl">
                            Preguntas frecuentes de nuestros huéspedes
                        </h3>
                    </div>

                    <Accordion className="w-full space-y-3">
                        <AccordionItem
                            value="item-1"
                            className="rounded-2xl border border-border bg-card px-5 shadow-xs"
                        >
                            <AccordionTrigger className="py-4 text-xs font-bold text-foreground hover:no-underline sm:text-sm">
                                <div className="flex items-center gap-2.5">
                                    <Clock className="size-4 shrink-0 text-primary dark:text-rose-400" />
                                    <span>
                                        ¿Cuáles son los horarios de Check-in y
                                        Check-out?
                                    </span>
                                </div>
                            </AccordionTrigger>
                            <AccordionContent className="pb-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                El horario estándar de Check-in es a partir de
                                las 2:00 PM y el Check-out hasta las 12:00 PM
                                del mediodía. Si necesitas ingreso temprano o
                                salida tardía, comunícate con recepción y te
                                atenderemos según disponibilidad.
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem
                            value="item-2"
                            className="rounded-2xl border border-border bg-card px-5 shadow-xs"
                        >
                            <AccordionTrigger className="py-4 text-xs font-bold text-foreground hover:no-underline sm:text-sm">
                                <div className="flex items-center gap-2.5">
                                    <Car className="size-4 shrink-0 text-emerald-500" />
                                    <span>
                                        ¿El hotel cuenta con estacionamiento
                                        privado y seguro?
                                    </span>
                                </div>
                            </AccordionTrigger>
                            <AccordionContent className="pb-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                Sí, contamos con parqueo privado gratuito dentro
                                de las instalaciones para todos nuestros
                                huéspedes, con portón cerrado, vigilancia y
                                cámaras de seguridad las 24 horas del día.
                            </AccordionContent>
                        </AccordionItem>

                        <AccordionItem
                            value="item-3"
                            className="rounded-2xl border border-border bg-card px-5 shadow-xs"
                        >
                            <AccordionTrigger className="py-4 text-xs font-bold text-foreground hover:no-underline sm:text-sm">
                                <div className="flex items-center gap-2.5">
                                    <CreditCard className="size-4 shrink-0 text-amber-500" />
                                    <span>
                                        ¿Qué métodos de pago son aceptados?
                                    </span>
                                </div>
                            </AccordionTrigger>
                            <AccordionContent className="pb-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                Aceptamos transferencias bancarias nacionales
                                (BAC, Banpro, LAFISE), tarjetas de crédito y
                                débito (Visa, MasterCard, American Express) y
                                pagos en efectivo tanto en Dólares (USD) como en
                                Córdobas (NIO).
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </div>
        </section>
    );
};

export default BookingCtaBanner;
