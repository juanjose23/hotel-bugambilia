import { Coffee, Sun, Moon } from 'lucide-react';
import type { RestauranteData } from '../types';

interface PropsRestauranteHorarios {
    restaurante: RestauranteData;
}

export const RestauranteHorarios = ({
    restaurante,
}: PropsRestauranteHorarios) => {
    return (
        <section
            aria-label="Horarios de Atención del Restaurante"
            className="border-b border-border bg-card/40 py-10 font-sans sm:py-12"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-6">
                    {/* Turno Desayuno */}
                    <div className="flex items-start gap-4 rounded-2xl border border-border/80 bg-card p-5 shadow-xs transition-all hover:border-amber-500/40 hover:bg-muted/40">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 shadow-xs">
                            <Coffee className="size-6" />
                        </div>
                        <div>
                            <span className="text-[10px] font-black tracking-wider text-amber-500 uppercase">
                                Mañanas
                            </span>
                            <h3 className="text-sm font-bold text-foreground">
                                Desayunos Buffet & Carta
                            </h3>
                            <p className="mt-1 text-xs font-semibold text-muted-foreground">
                                {restaurante.horario_desayuno}
                            </p>
                        </div>
                    </div>

                    {/* Turno Almuerzo */}
                    <div className="flex items-start gap-4 rounded-2xl border border-border/80 bg-card p-5 shadow-xs transition-all hover:border-primary/40 hover:bg-muted/40">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-xs dark:text-rose-400">
                            <Sun className="size-6" />
                        </div>
                        <div>
                            <span className="text-[10px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                                Mediodía
                            </span>
                            <h3 className="text-sm font-bold text-foreground">
                                Almuerzos Ejecutivos & Menú
                            </h3>
                            <p className="mt-1 text-xs font-semibold text-muted-foreground">
                                {restaurante.horario_almuerzo}
                            </p>
                        </div>
                    </div>

                    {/* Turno Cena */}
                    <div className="flex items-start gap-4 rounded-2xl border border-border/80 bg-card p-5 shadow-xs transition-all hover:border-indigo-500/40 hover:bg-muted/40">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 shadow-xs">
                            <Moon className="size-6" />
                        </div>
                        <div>
                            <span className="text-[10px] font-black tracking-wider text-indigo-500 uppercase">
                                Noches
                            </span>
                            <h3 className="text-sm font-bold text-foreground">
                                Cenas Gourmet & Bar Lounge
                            </h3>
                            <p className="mt-1 text-xs font-semibold text-muted-foreground">
                                {restaurante.horario_cena}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default RestauranteHorarios;
