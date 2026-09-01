import {
    MessageSquareText,
    Phone,
    MapPin,
    Clock,
    HeartHandshake,
    MessageCircle,
} from 'lucide-react';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const ContactoHero = () => {
    const { hotel } = usePropiedadesPagina();
    const telefonoWhatsApp = (hotel?.whatsapp || '+50587136805').replace(
        /\D/g,
        '',
    );

    return (
        <section
            aria-label="Encabezado de Contacto"
            className="relative bg-background pt-4 pb-8 md:py-14"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="grid grid-cols-1 items-center gap-8 lg:grid-cols-12 lg:gap-12">
                    {/* 1. EN MÓVIL: Imagen arriba para impacto visual. EN DESKTOP: Columna derecha (5 cols) */}
                    <div className="relative order-1 lg:order-2 lg:col-span-5">
                        <div className="relative mx-auto max-w-md overflow-hidden rounded-3xl border border-border bg-card shadow-xl lg:max-w-none">
                            <div className="relative aspect-16/10 w-full overflow-hidden bg-muted sm:aspect-4/3">
                                <img
                                    src="/images/hero-secondary.webp"
                                    alt="Recepción y jardines de Hotel Bugambilias Estelí"
                                    className="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                                    loading="eager"
                                />
                                <div
                                    aria-hidden="true"
                                    className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"
                                />
                                <div className="absolute right-3 bottom-3 left-3 text-white sm:right-4 sm:bottom-4 sm:left-4">
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-white uppercase backdrop-blur-md">
                                        <MapPin className="size-3 text-rose-300" />
                                        <span>Salida Sur • Estelí</span>
                                    </div>
                                    <p className="mt-1 text-sm font-black text-white sm:text-base">
                                        Recepción & Asistencia Personalizada
                                    </p>
                                </div>
                            </div>

                            {/* Badge Flotante Superior */}
                            <div className="absolute top-3 right-3 flex items-center gap-2 rounded-2xl border border-border/80 bg-card/95 px-3 py-1.5 shadow-lg backdrop-blur-md">
                                <span className="size-2 animate-pulse rounded-full bg-emerald-500" />
                                <span className="text-[11px] font-black text-foreground">
                                    Abierto 24/7
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* 2. Contenido Editorial con Contraste Nítido (7 cols) */}
                    <div className="order-2 text-center lg:order-1 lg:col-span-7 lg:text-left">
                        {/* Badge de Sección */}
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3.5 py-1 text-primary dark:border-rose-500/40 dark:bg-rose-950/60 dark:text-rose-200">
                            <MessageSquareText
                                className="size-3.5 text-primary dark:text-rose-300"
                                aria-hidden="true"
                            />
                            <span className="text-[11px] font-black tracking-wider uppercase">
                                Atención Inmediata
                            </span>
                        </div>

                        {/* Título Principal */}
                        <h1 className="mt-4 text-2xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                            Comunícate con{' '}
                            <span className="text-primary dark:text-rose-400">
                                Hotel Bugambilias
                            </span>
                        </h1>

                        {/* Descripción */}
                        <p className="mt-3.5 max-w-xl text-xs leading-relaxed text-muted-foreground sm:text-sm md:text-base">
                            ¿Tienes dudas sobre disponibilidad de habitaciones,
                            paquetes para eventos o requieres asistencia con tu
                            reserva en Estelí? Nuestro equipo está a tu entera
                            disposición para atenderte con calidez y prontitud.
                        </p>

                        {/* Botones de Contacto Rápido */}
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                            <a
                                href={`https://wa.me/${telefonoWhatsApp}?text=${encodeURIComponent('Hola Hotel Bugambilias, deseo comunicarme con recepción.')}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-2.5 text-xs font-bold text-white shadow-md transition-transform hover:scale-105 hover:bg-emerald-700 active:scale-95"
                            >
                                <MessageCircle className="size-4" />
                                <span>Chatear por WhatsApp</span>
                            </a>

                            <a
                                href="tel:+50587136805"
                                className="inline-flex items-center gap-2 rounded-full border border-border bg-card px-5 py-2.5 text-xs font-bold text-foreground shadow-xs transition-colors hover:bg-accent active:scale-95"
                            >
                                <Phone className="size-3.5 text-primary dark:text-rose-400" />
                                <span>Llamar al +505 8713 6805</span>
                            </a>
                        </div>

                        {/* Badges de Confianza */}
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-4 border-t border-border pt-4 text-xs font-medium text-muted-foreground sm:gap-6 lg:justify-start">
                            <div className="flex items-center gap-1.5">
                                <Clock
                                    className="size-3.5 text-amber-500"
                                    aria-hidden="true"
                                />
                                <span className="text-foreground">
                                    Recepción Continua 24/7
                                </span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <HeartHandshake
                                    className="size-3.5 text-primary dark:text-rose-400"
                                    aria-hidden="true"
                                />
                                <span className="text-foreground">
                                    Atención en Estelí
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default ContactoHero;
