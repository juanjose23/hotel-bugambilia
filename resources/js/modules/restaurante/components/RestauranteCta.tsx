import { Link } from '@inertiajs/react';
import { PhoneCall, CalendarCheck, Calendar } from 'lucide-react';
import { buttonVariants } from '@/modules/shared/components/ui/button';
import type { RestauranteData } from '../types';

interface PropsRestauranteCta {
    restaurante: RestauranteData;
}

export const RestauranteCta = ({ restaurante }: PropsRestauranteCta) => {
    const telefono = restaurante.telefono_reservas || '+505 8713 6805';

    return (
        <section className="border-t border-border bg-gradient-to-b from-card to-muted/40 py-16">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-4xl rounded-3xl border border-primary/20 bg-gradient-to-tr from-primary/10 via-card to-amber-500/10 p-8 text-center shadow-lg md:p-12">
                    <div className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-md">
                        <CalendarCheck className="size-6" />
                    </div>

                    <h2 className="mt-4 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        ¿Deseas Reservar una Mesa en {restaurante.nombre}?
                    </h2>

                    <p className="mx-auto mt-2 max-w-xl text-xs text-muted-foreground sm:text-sm">
                        Atendemos reservaciones por turno horario para desayunos
                        ejecutivos, almuerzos familiares, cenas románticas y
                        eventos especiales.
                    </p>

                    <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <Link
                            href="/restaurante/reservar"
                            className={buttonVariants({
                                size: 'lg',
                                className:
                                    'cursor-pointer bg-primary font-bold text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95',
                            })}
                        >
                            <Calendar className="size-4" />
                            <span>Reservar Mesa en el Plano</span>
                        </Link>

                        <a
                            href={`tel:${telefono.replace(/\s+/g, '')}`}
                            className={buttonVariants({
                                variant: 'outline',
                                size: 'lg',
                                className:
                                    'cursor-pointer border-border bg-card text-foreground transition-all hover:bg-muted active:scale-95',
                            })}
                        >
                            <PhoneCall className="size-4 text-primary dark:text-rose-400" />
                            <span>Llamar al {telefono}</span>
                        </a>

                        <Link
                            href="/contacto"
                            className={buttonVariants({
                                variant: 'ghost',
                                size: 'lg',
                                className:
                                    'cursor-pointer text-muted-foreground transition-all hover:text-foreground active:scale-95',
                            })}
                        >
                            <span>Ver Ubicación & Mapa →</span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default RestauranteCta;
