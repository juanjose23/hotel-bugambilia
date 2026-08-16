import { Sparkles, ShieldCheck, Coffee } from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

export const BloquesCaracteristicasEspacio = () => {
    return (
        <div className="grid grid-cols-1 gap-4 font-sans sm:grid-cols-3">
            <Card className="rounded-2xl border border-border/70 bg-card p-4 shadow-xs">
                <CardContent className="flex items-center gap-3 p-0">
                    <div className="flex size-10 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                        <Sparkles className="size-5" />
                    </div>
                    <div>
                        <span className="block text-xs font-black text-foreground">
                            Montaje Personalizable
                        </span>
                        <span className="text-[11px] font-medium text-muted-foreground">
                            Auditorio, U, Cóctel o Banquete
                        </span>
                    </div>
                </CardContent>
            </Card>

            <Card className="rounded-2xl border border-border/70 bg-card p-4 shadow-xs">
                <CardContent className="flex items-center gap-3 p-0">
                    <div className="flex size-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <ShieldCheck className="size-5" />
                    </div>
                    <div>
                        <span className="block text-xs font-black text-foreground">
                            Atención Concierge 24/7
                        </span>
                        <span className="text-[11px] font-medium text-muted-foreground">
                            Asistencia técnica para su evento
                        </span>
                    </div>
                </CardContent>
            </Card>

            <Card className="rounded-2xl border border-border/70 bg-card p-4 shadow-xs">
                <CardContent className="flex items-center gap-3 p-0">
                    <div className="flex size-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <Coffee className="size-5" />
                    </div>
                    <div>
                        <span className="block text-xs font-black text-foreground">
                            Catering & Coffee Break
                        </span>
                        <span className="text-[11px] font-medium text-muted-foreground">
                            Servicio gastronómico opcional
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default BloquesCaracteristicasEspacio;
