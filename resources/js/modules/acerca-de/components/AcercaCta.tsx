import { Link } from '@inertiajs/react';
import { CalendarCheck, MessageSquareText, Phone } from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';

export const AcercaCta = () => {
    return (
        <section
            aria-label="Llamado a reservar"
            className="bg-background py-10 md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="relative overflow-hidden rounded-3xl border border-border bg-card p-8 text-center shadow-xs sm:p-12 md:p-14">
                    <div className="mx-auto max-w-2xl">
                        <span className="dark:bg-bugambilia-950/60 inline-block rounded-full border border-bugambilia-200 bg-bugambilia-50 px-3.5 py-1 text-[10px] font-black tracking-widest text-bugambilia-800 uppercase dark:border-bugambilia-800/60 dark:text-bugambilia-300">
                            Estelí te espera con los brazos abiertos
                        </span>
                        <h2 className="mt-4 text-2xl font-black tracking-tight text-foreground sm:text-3xl md:text-4xl">
                            Ven a vivir la experiencia de sentirte en casa
                        </h2>
                        <p className="mt-3 text-xs leading-relaxed text-muted-foreground sm:text-sm md:text-base">
                            Ya sea por un viaje de placer, descanso familiar o
                            compromisos de negocios en el norte de Nicaragua, en
                            Hotel Bugambilias cuidamos cada detalle para ti.
                        </p>

                        <div className="mt-7 flex flex-wrap items-center justify-center gap-3">
                            <Link
                                href="/habitaciones"
                                aria-label="Ver disponibilidad y tarifas de habitaciones"
                                className={buttonVariants({
                                    size: 'default',
                                    className:
                                        'cursor-pointer rounded-full bg-bugambilia-600 px-6 text-xs font-black text-white shadow-sm hover:bg-bugambilia-700 active:scale-95',
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
                                aria-label="Escribir al personal de recepción del hotel"
                                className={buttonVariants({
                                    variant: 'outline',
                                    size: 'default',
                                    className:
                                        'cursor-pointer rounded-full border-border bg-card px-5 text-xs font-bold text-foreground shadow-xs hover:bg-accent active:scale-95',
                                })}
                            >
                                <MessageSquareText
                                    className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400"
                                    aria-hidden="true"
                                />
                                <span>Contáctanos</span>
                            </Link>

                            <a
                                href="tel:+50587136805"
                                aria-label="Llamar directamente al hotel"
                                className={buttonVariants({
                                    variant: 'ghost',
                                    size: 'default',
                                    className:
                                        'cursor-pointer rounded-full px-4 text-xs font-bold text-muted-foreground hover:text-foreground',
                                })}
                            >
                                <Phone
                                    className="size-3.5 text-bugambilia-600 dark:text-bugambilia-400"
                                    aria-hidden="true"
                                />
                                <span>+505 8713 6805</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default AcercaCta;
