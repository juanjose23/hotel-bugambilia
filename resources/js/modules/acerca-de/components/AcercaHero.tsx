import { Link } from '@inertiajs/react';
import {
    CalendarCheck,
    MessageSquare,
    MapPin,
    HeartHandshake,
    Coffee,
    Building2,
} from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';

export const AcercaHero = () => {
    return (
        <section
            aria-label="Presentación de Hotel Bugambilias"
            className="relative bg-background pt-4 pb-10 md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="grid grid-cols-1 items-center gap-8 lg:grid-cols-12 lg:gap-12">
                    {/* 1. EN MÓVIL: Fotografía destacada arriba para impacto visual instantáneo */}
                    <div className="relative order-1 lg:order-2 lg:col-span-5">
                        <div className="relative mx-auto max-w-md overflow-hidden rounded-3xl border border-border bg-card shadow-xl lg:max-w-none">
                            <div className="relative aspect-16/10 w-full overflow-hidden bg-muted sm:aspect-4/3">
                                <img
                                    src="/images/hero-main.webp"
                                    alt="Fachada y jardines de Hotel Bugambilias Estelí"
                                    className="h-full w-full object-cover"
                                    loading="eager"
                                />
                                <div
                                    aria-hidden="true"
                                    className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"
                                />
                                <div className="absolute right-3 bottom-3 left-3 text-white sm:right-4 sm:bottom-4 sm:left-4">
                                    <div className="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-white uppercase backdrop-blur-md">
                                        <MapPin className="size-3 text-rose-300" />
                                        <span>Estelí, Nicaragua</span>
                                    </div>
                                    <p className="mt-1 text-sm font-black text-white sm:text-base">
                                        Patios Coloniales & Jardines Florecidos
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* 2. Contenido Editorial con Contraste y Legibilidad Nítida */}
                    <div className="order-2 text-center lg:order-1 lg:col-span-7 lg:text-left">
                        {/* Badge de Marca con Alto Contraste en Claro y Oscuro */}
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3.5 py-1 text-primary dark:border-rose-500/40 dark:bg-rose-950/60 dark:text-rose-200">
                            <Building2 className="size-3.5 text-primary dark:text-rose-300" />
                            <span className="text-[11px] font-black tracking-wider uppercase">
                                Hotel Bugambilias • Estelí
                            </span>
                        </div>

                        {/* Título Principal de Alto Impacto */}
                        <h1 className="mt-4 text-2xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                            Un refugio de hospitalidad donde{' '}
                            <span className="text-primary dark:text-rose-400">
                                te sientes en casa
                            </span>
                        </h1>

                        {/* Descripción Legible */}
                        <p className="mt-3.5 max-w-xl text-xs leading-relaxed text-muted-foreground sm:text-sm md:text-base">
                            Desde el aroma a café norteño recién colado por las
                            mañanas hasta la frescura de nuestros jardines, en
                            Hotel Bugambilias te recibimos con la genuina
                            calidez esteliana para que descanses y disfrutes en
                            total tranquilidad.
                        </p>

                        {/* Calificación 5 Estrellas */}
                        <div
                            className="mt-4 flex flex-wrap items-center justify-center gap-2 lg:justify-start"
                            aria-label="Calificación de 5 estrellas"
                        >
                            <span className="text-xs text-muted-foreground">
                                • Tradición y descanso en el norte de Nicaragua
                            </span>
                        </div>

                        {/* Botones de Acción Accesibles */}
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                            <Link
                                href="/habitaciones"
                                aria-label="Ver habitaciones disponibles"
                                className={buttonVariants({
                                    size: 'default',
                                    className:
                                        'cursor-pointer rounded-full bg-primary px-6 text-xs font-black text-primary-foreground shadow-sm hover:bg-primary/90 active:scale-95',
                                })}
                            >
                                <CalendarCheck
                                    className="size-3.5"
                                    aria-hidden="true"
                                />
                                <span>Ver Habitaciones</span>
                            </Link>

                            <Link
                                href="/contacto"
                                aria-label="Contactar al hotel"
                                className={buttonVariants({
                                    variant: 'outline',
                                    size: 'default',
                                    className:
                                        'cursor-pointer rounded-full border-border bg-card px-5 text-xs font-bold text-foreground shadow-xs hover:bg-accent active:scale-95',
                                })}
                            >
                                <MessageSquare
                                    className="size-3.5 text-primary dark:text-rose-400"
                                    aria-hidden="true"
                                />
                                <span>Contáctanos</span>
                            </Link>
                        </div>

                        {/* Badges de Confianza con Íconos y Textos Claros */}
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-4 border-t border-border pt-4 text-xs font-medium text-muted-foreground sm:gap-6 lg:justify-start">
                            <div className="flex items-center gap-1.5">
                                <Coffee
                                    className="size-3.5 text-amber-500"
                                    aria-hidden="true"
                                />
                                <span className="text-foreground">
                                    Café de Cortesía
                                </span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <HeartHandshake
                                    className="size-3.5 text-primary dark:text-rose-400"
                                    aria-hidden="true"
                                />
                                <span className="text-foreground">
                                    Atención Personalizada
                                </span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <MapPin
                                    className="size-3.5 text-emerald-500"
                                    aria-hidden="true"
                                />
                                <span className="text-foreground">
                                    Salida Sur, Estelí
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default AcercaHero;
