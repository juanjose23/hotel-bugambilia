import { Phone, Mail, MapPin, MessageCircle, ArrowUpRight } from 'lucide-react';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const ContactoInfoCards = () => {
    const { hotel } = usePropiedadesPagina();

    const telefono = hotel?.telefono || '+505 8713 6805';
    const telefonoWhatsApp = (hotel?.whatsapp || '+50587136805').replace(
        /\D/g,
        '',
    );
    const email = hotel?.email || 'recepcion@bugambiliashotel.com';

    const CARDS = [
        {
            id: '1',
            titulo: 'WhatsApp Directo',
            subtitulo: 'Cotizaciones y reservas inmediatas',
            valor: '+505 8713 6805',
            cta: 'Chatear ahora',
            enlace: `https://wa.me/${telefonoWhatsApp}?text=${encodeURIComponent('Hola Hotel Bugambilias, deseo solicitar información.')}`,
            icono: MessageCircle,
            estiloIcono:
                'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/25',
            colorTexto: 'text-emerald-600 dark:text-emerald-400',
            badge: 'Respuesta rápida',
        },
        {
            id: '2',
            titulo: 'Llamada a Recepción',
            subtitulo: 'Asistencia y consultas 24/7',
            valor: telefono,
            cta: 'Llamar al hotel',
            enlace: `tel:${telefono.replace(/\s+/g, '')}`,
            icono: Phone,
            estiloIcono:
                'bg-primary/15 text-primary dark:text-rose-400 border-primary/25',
            colorTexto: 'text-primary dark:text-rose-400',
            badge: '24 Horas',
        },
        {
            id: '3',
            titulo: 'Correo Electrónico',
            subtitulo: 'Eventos, bodas y grupos',
            valor: email,
            cta: 'Enviar correo',
            enlace: `mailto:${email}`,
            icono: Mail,
            estiloIcono:
                'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/25',
            colorTexto: 'text-amber-600 dark:text-amber-400',
            badge: 'Corporativo',
        },
        {
            id: '4',
            titulo: 'Ubicación en Estelí',
            subtitulo: 'Salida Sur, zona tranquila',
            valor: 'Salida Sur, Estelí, Nicaragua',
            cta: 'Ver en GPS',
            enlace: 'https://maps.google.com/?q=Hotel+Bugambilias+Esteli+Nicaragua',
            icono: MapPin,
            estiloIcono:
                'bg-sky-500/15 text-sky-600 dark:text-sky-400 border-sky-500/25',
            colorTexto: 'text-sky-600 dark:text-sky-400',
            badge: 'Fácil acceso',
        },
    ];

    return (
        <section
            aria-label="Canales de Atención Directa"
            className="bg-background py-6 md:py-8"
        >
            <div className="container mx-auto px-4 sm:px-6">
                {/* Grilla interactiva con swipe en móvil */}
                <div className="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4">
                    {CARDS.map((card) => {
                        const Icono = card.icono;

                        return (
                            <a
                                key={card.id}
                                href={card.enlace}
                                target={
                                    card.id === '1' || card.id === '4'
                                        ? '_blank'
                                        : undefined
                                }
                                rel={
                                    card.id === '1' || card.id === '4'
                                        ? 'noopener noreferrer'
                                        : undefined
                                }
                                className="group relative flex w-[260px] shrink-0 snap-center flex-col justify-between overflow-hidden rounded-3xl border border-border/90 bg-card/80 p-5.5 shadow-xs backdrop-blur-md transition-all duration-300 hover:-translate-y-1.5 hover:border-primary/50 hover:shadow-xl sm:w-auto dark:bg-card/70 dark:hover:border-rose-500/50"
                            >
                                <div>
                                    {/* Cabecera de la tarjeta con Icono destacado y Badge */}
                                    <div className="flex items-center justify-between">
                                        <div
                                            className={`flex size-12 items-center justify-center rounded-2xl border ${card.estiloIcono} shadow-xs transition-transform duration-300 group-hover:scale-110`}
                                        >
                                            <Icono className="size-6" />
                                        </div>

                                        <span className="rounded-full border border-border/80 bg-background/80 px-2.5 py-0.5 text-[10px] font-black text-muted-foreground uppercase backdrop-blur-md">
                                            {card.badge}
                                        </span>
                                    </div>

                                    {/* Títulos */}
                                    <h3 className="mt-4 text-sm font-black text-foreground sm:text-base">
                                        {card.titulo}
                                    </h3>
                                    <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                                        {card.subtitulo}
                                    </p>
                                </div>

                                {/* Valor y Botón de Acción */}
                                <div className="mt-5 border-t border-border/60 pt-3.5">
                                    <span
                                        className={`block truncate text-xs font-black ${card.colorTexto}`}
                                    >
                                        {card.valor}
                                    </span>

                                    <div className="mt-2.5 flex items-center justify-between text-xs font-bold text-foreground transition-colors group-hover:text-primary dark:group-hover:text-rose-400">
                                        <span>{card.cta}</span>
                                        <ArrowUpRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                    </div>
                                </div>
                            </a>
                        );
                    })}
                </div>
            </div>
        </section>
    );
};

export default ContactoInfoCards;
