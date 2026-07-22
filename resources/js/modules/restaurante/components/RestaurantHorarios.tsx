import { Coffee, UtensilsCrossed, Wine, Clock, Check } from 'lucide-react';
import type { RestauranteData } from '@/modules/restaurante/types';

interface RestaurantHorariosProps {
    restaurante: RestauranteData;
}

export default function RestaurantHorarios({
    restaurante,
}: RestaurantHorariosProps) {
    const horariosList = [
        {
            label: 'Desayunos',
            horario: restaurante.horario_desayuno || '07:00 - 10:30 AM',
            icon: Coffee,
            badge: 'Buffet & A la carta',
            descripcion:
                'Nacatamales tradicionales los fines de semana, café de estelí recién colado, gallo pinto y frutas tropicales.',
            destacado: 'Incluido en tarifa de hospedaje (según plan)',
        },
        {
            label: 'Almuerzos Ejecutivos',
            horario: restaurante.horario_almuerzo || '12:00 - 03:30 PM',
            icon: UtensilsCrossed,
            badge: 'Menú Ejecutivo',
            descripcion:
                'Cortes a la plancha, ensaladas frescas, pescados de la costa nicaragüense y sopas tradicionales.',
            destacado: 'Ambiente climatizado o terraza exterior',
        },
        {
            label: 'Cenas & Coctelería',
            horario: restaurante.horario_cena || '06:00 - 10:00 PM',
            icon: Wine,
            badge: 'Gourmet & Veladas',
            descripcion:
                'Cenas románticas a la luz de velas, tablas de quesos, coctelería de autor y repostería artesanal.',
            destacado: 'Música en vivo los fines de semana',
        },
    ];

    return (
        <section className="border-t border-border/40 bg-muted/10 py-20 font-sans">
            <div className="container mx-auto max-w-6xl px-4">
                <div className="mx-auto mb-14 max-w-2xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-widest text-amber-600 uppercase dark:text-amber-400">
                        <Clock className="h-3.5 w-3.5" />
                        Servicio Diario
                    </div>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-5xl">
                        Horarios de Atención
                    </h2>
                    <p className="text-base text-muted-foreground md:text-lg">
                        Atendemos tanto a huéspedes del hotel como a visitantes
                        externos en cualquiera de nuestros 3 tiempos de comida.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    {horariosList.map((item, idx) => {
                        const Icon = item.icon;

                        return (
                            <div
                                key={idx}
                                className="flex flex-col justify-between rounded-3xl border border-border/80 bg-card p-8 transition-all duration-300 hover:border-amber-500/50 hover:shadow-xl"
                            >
                                <div>
                                    <div className="mb-6 flex items-center justify-between gap-2">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                            <Icon className="h-6 w-6" />
                                        </div>
                                        <span className="rounded-full bg-muted px-3 py-1 text-[10px] font-black tracking-wider text-foreground uppercase">
                                            {item.badge}
                                        </span>
                                    </div>

                                    <h3 className="mb-2 text-xl font-black text-foreground">
                                        {item.label}
                                    </h3>
                                    <p className="mb-4 text-2xl font-black tracking-tight text-amber-600 dark:text-amber-400">
                                        {item.horario}
                                    </p>

                                    <p className="mb-6 text-xs leading-relaxed text-muted-foreground">
                                        {item.descripcion}
                                    </p>
                                </div>

                                <div className="flex items-center gap-2 border-t border-border/40 pt-4 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <Check className="h-4 w-4 shrink-0" />
                                    <span>{item.destacado}</span>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
