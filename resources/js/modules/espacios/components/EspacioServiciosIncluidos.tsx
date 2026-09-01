import {
    CheckCircle2,
    ShieldCheck,
    Wifi,
    Wind,
    Volume2,
    Video,
} from 'lucide-react';
import type { ServicioIncluidoEspacio } from '../types';

interface PropsEspacioServiciosIncluidos {
    servicios?: ServicioIncluidoEspacio[];
}

const AMENIDADES_DEFAULT = [
    {
        nombre: 'Climatización Total',
        icono: Wind,
        desc: 'Aire acondicionado de alta capacidad',
    },
    {
        nombre: 'WiFi de Alta Velocidad',
        icono: Wifi,
        desc: 'Conexión fibra óptica para invitados',
    },
    {
        nombre: 'Sistema de Audio',
        icono: Volume2,
        desc: 'Micrófonos y sonido ambiental',
    },
    {
        nombre: 'Proyección & Pantallas',
        icono: Video,
        desc: 'Equipamiento audiovisual corporativo',
    },
];

export const EspacioServiciosIncluidos = ({
    servicios = [],
}: PropsEspacioServiciosIncluidos) => {
    return (
        <div className="mt-10 font-sans">
            <div className="mb-6">
                <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-wider text-bugambilia-600 uppercase dark:text-bugambilia-400">
                    <ShieldCheck className="size-3.5" />
                    <span>Equipamiento & Amenidades</span>
                </div>
                <h3 className="mt-1 text-xl font-black text-foreground">
                    Lo que incluye este espacio
                </h3>
            </div>

            {servicios.length > 0 ? (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {servicios.map((s) => (
                        <div
                            key={s.id}
                            className="flex items-start gap-3 rounded-2xl border border-border bg-card p-4 shadow-xs"
                        >
                            <CheckCircle2 className="size-5 shrink-0 text-emerald-500" />
                            <div>
                                <h4 className="text-xs font-bold text-foreground">
                                    {s.nombre}
                                </h4>
                                {s.descripcion && (
                                    <p className="mt-0.5 text-[11px] text-muted-foreground">
                                        {s.descripcion}
                                    </p>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {AMENIDADES_DEFAULT.map((item, idx) => {
                        const Icono = item.icono;

                        return (
                            <div
                                key={idx}
                                className="flex items-start gap-3 rounded-2xl border border-border bg-card p-4 shadow-xs"
                            >
                                <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                    <Icono className="size-4" />
                                </div>
                                <div>
                                    <h4 className="text-xs font-bold text-foreground">
                                        {item.nombre}
                                    </h4>
                                    <p className="mt-0.5 text-[11px] text-muted-foreground">
                                        {item.desc}
                                    </p>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
};

export default EspacioServiciosIncluidos;
