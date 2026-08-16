import { Users, LayoutGrid, Award, ShieldCheck } from 'lucide-react';
import { Card } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesEsquemasMontajeEspacio {
    capacidadMaxima: number;
    capacidadMesas?: number | string;
}

export const EsquemasMontajeEspacio = ({
    capacidadMaxima = 50,
    capacidadMesas,
}: PropiedadesEsquemasMontajeEspacio) => {
    const numMax = Number(capacidadMaxima) || 50;
    const numMesas = Number(capacidadMesas) || Math.round(numMax * 0.7);

    const opcionesMontaje = [
        {
            titulo: 'Auditorio / Conferencia',
            capacidad: `${numMax} personas`,
            descripcion:
                'Sillas alineadas frente al escenario o área de presentación principal.',
            icono: Users,
            badge: 'Máximo Aforo',
        },
        {
            titulo: 'Banquete / Cena Formal',
            capacidad: `${numMesas} personas`,
            descripcion:
                'Mesas redondas o rectangulares distribuidas con pista de baile o vestíbulo.',
            icono: LayoutGrid,
            badge: 'Eventos Sociales',
        },
        {
            titulo: 'Cóctel & Recepción',
            capacidad: `${Math.round(numMax * 1.2)} personas`,
            descripcion:
                'Formato de pie con mesas altas tipo bar para interacción social fluida.',
            icono: Award,
            badge: 'Corporativo / Gala',
        },
        {
            titulo: 'Mesa U / Conferencia VIP',
            capacidad: `${Math.round(numMax * 0.4)} personas`,
            descripcion:
                'Distribución ejecutiva alrededor de mesa central con conectividad.',
            icono: ShieldCheck,
            badge: 'Reuniones Directorio',
        },
    ];

    return (
        <Card className="rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xs md:p-8">
            <div className="mb-6">
                <span className="block text-[11px] font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                    Versatilidad de Montaje
                </span>
                <h3 className="text-xl font-black text-foreground sm:text-2xl">
                    Distribución de Mesas & Formatos de Evento
                </h3>
                <p className="mt-1 text-xs font-medium text-muted-foreground">
                    Nuestro equipo técnico adapta el montaje del salón según los
                    requerimientos específicos de su actividad.
                </p>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {opcionesMontaje.map((opt, idx) => {
                    const IconoComp = opt.icono;

                    return (
                        <div
                            key={idx}
                            className="group flex flex-col justify-between rounded-2xl border border-border/70 bg-muted/20 p-4 transition-all duration-300 hover:border-bugambilia-500/40 hover:bg-card hover:shadow-md"
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div className="flex size-10 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 transition-colors group-hover:bg-bugambilia-600 group-hover:text-white dark:text-bugambilia-400">
                                    <IconoComp className="size-5" />
                                </div>
                                <span className="rounded-full bg-muted px-2.5 py-0.5 text-[10px] font-extrabold text-foreground">
                                    {opt.badge}
                                </span>
                            </div>

                            <div className="mt-3">
                                <h4 className="text-sm font-black text-foreground">
                                    {opt.titulo}
                                </h4>
                                <span className="block text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                                    Hasta {opt.capacidad}
                                </span>
                                <p className="mt-1 text-[11px] leading-relaxed text-muted-foreground">
                                    {opt.descripcion}
                                </p>
                            </div>
                        </div>
                    );
                })}
            </div>
        </Card>
    );
};

export default EsquemasMontajeEspacio;
