import { usePage, Link } from '@inertiajs/react';
import { Phone, Mail, MapPin, Clock, Sparkles, ArrowRight } from 'lucide-react';

interface ContactInfoProps {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
}

export default function ContactSection({ hotelInfo }: ContactInfoProps) {
    const pageProps = usePage().props;
    const name =
        hotelInfo?.nombre || pageProps.hotel?.name || 'Hotel Bugambilias';
    const telefono =
        hotelInfo?.telefono || pageProps.hotel?.telefono || '+505 8713 6805';
    const email =
        hotelInfo?.email ||
        pageProps.hotel?.email ||
        'recepcion@bugambiliashotel.com';
    const direccion =
        hotelInfo?.direccion ||
        pageProps.hotel?.direccion ||
        'Estelí, Nicaragua';

    const contactItems = [
        {
            icon: Phone,
            title: 'Reservaciones Directas',
            details: telefono,
            description: 'Atención telefónica y WhatsApp 24/7',
        },
        {
            icon: Mail,
            title: 'Correo Concierge',
            details: email,
            description: 'Respuesta garantizada en menos de 2 horas',
        },
        {
            icon: MapPin,
            title: 'Ubicación Privilegiada',
            details: direccion,
            description: 'Salida Sur, Estelí',
        },
        {
            icon: Clock,
            title: 'Horario de Recepción',
            details: 'Check-in 14:00 / Out 12:00',
            description: 'Flexibilidad y custodia de equipaje',
        },
    ];

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                        <Sparkles className="h-3.5 w-3.5" />
                        Atención Personalizada
                    </div>
                    <h2 className="mb-4 text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                        Estamos a su{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Disposición
                        </span>
                    </h2>
                    <p className="text-base font-medium text-muted-foreground sm:text-lg">
                        Comuníquese con nuestro equipo de concierge para
                        asistencia personalizada, reservas de eventos o
                        requerimientos especiales.
                    </p>
                </div>

                {/* Contact Info Cards */}
                <div className="mb-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {contactItems.map((item, index) => (
                        <div
                            key={index}
                            className="shadow-airbnb hover:shadow-airbnb-hover flex flex-col justify-between rounded-3xl border border-border/80 bg-card p-6 text-center transition-all duration-300 hover:-translate-y-1"
                        >
                            <div>
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10">
                                    <item.icon className="h-6 w-6 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <h3 className="mb-2 text-sm font-extrabold text-foreground">
                                    {item.title}
                                </h3>
                                <p className="mb-1 text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                    {item.details}
                                </p>
                                <p className="text-[11px] text-muted-foreground">
                                    {item.description}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Callout Box */}
                <div className="from-bugambilia-950 mx-auto max-w-4xl rounded-3xl border border-bugambilia-900/40 bg-gradient-to-r via-gray-900 to-gray-950 p-8 text-center text-white shadow-2xl md:p-12">
                    <h3 className="mb-3 text-2xl font-black sm:text-3xl">
                        ¿Tiene alguna consulta específica sobre {name}?
                    </h3>
                    <p className="mx-auto mb-8 max-w-xl text-sm text-white/80">
                        Nuestro equipo de atención al huésped está listo para
                        planificar cada detalle de su próxima visita.
                    </p>
                    <div className="flex flex-col justify-center gap-3 sm:flex-row">
                        <a
                            href={`tel:${telefono.replace(/[^0-9+]/g, '')}`}
                            className="inline-flex items-center justify-center gap-2 rounded-full bg-bugambilia-600 px-8 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase shadow-lg transition-all duration-300 hover:scale-105 hover:bg-bugambilia-500"
                        >
                            <span>Llamar Ahora ({telefono})</span>
                            <ArrowRight className="h-4 w-4" />
                        </a>
                        <Link
                            href="/habitaciones"
                            className="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-8 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-colors hover:bg-white/20"
                        >
                            Ver Habitaciones
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    );
}
